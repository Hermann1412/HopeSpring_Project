# HopeSpring Project Flow Diagrams

This document explains how the HopeSpring project works through visual flow diagrams based on the current codebase.

## 1. End-to-End System Flow

```mermaid
flowchart TD
	A[User Opens App] --> B{Has Session mybook_userid?}
	B -- No --> C[Open Login or Signup]
	C --> C1[Submit Auth Form with CSRF]
	C1 --> C2{Credentials Valid?}
	C2 -- No --> C3[Show Validation Error]
	C2 -- Yes --> D[Create Session and Redirect]

	B -- Yes --> D
	D --> E[Load Feed index.php]
	E --> F[Login::check_login]
	F --> G[Fetch Profile and Following]
	G --> H[Fetch Feed Posts from MySQL]
	H --> I[Render HTML via app/partials]

	I --> J{User Action}
	J --> K[Create Post or Comment]
	J --> L[Like or Follow]
	J --> M[Open Messages]
	J --> N[Open Notifications]
	J --> O[Open Church Map]

	K --> K1[Validate CSRF]
	K1 --> K2[Post::create_post]
	K2 --> P[(MySQL + uploads)]
	P --> E

	L --> L1[like.php with type and id]
	L1 --> L2[Post::like_post]
	L2 --> L3[User::follow_user when type=user]
	L3 --> Q[(likes and notifications tables)]
	Q --> E

	M --> M1[messages.php]
	M1 --> M2[Message class send and fetch]
	M2 --> R[(messages and group tables)]
	R --> M1

	N --> N1[notifications.php]
	N1 --> N2[Read followed content and notifications]
	N2 --> S[(notifications data)]
	S --> N1

	O --> O1[church_map.php]
	O1 --> O2[Browser Geolocation]
	O2 --> O3[Overpass and Nominatim APIs]
	O3 --> O4[Leaflet map render]
```

## 2. Authentication Flow

```mermaid
flowchart TD
	A[Login or Signup Page] --> B[User submits form]
	B --> C[csrf_validate_request]
	C -->|Invalid| D[Reject request]
	C -->|Valid| E{Login or Signup}

	E -->|Signup| F[Signup::evaluate]
	F -->|Pass| G[Redirect to login.php]
	F -->|Fail| H[Show form errors]

	E -->|Login| I[Login::evaluate]
	I -->|Pass| J[Set session mybook_userid]
	J --> K[Redirect to profile.php]
	I -->|Fail| L[Show login error]
```

## 3. Feed and Social Interaction Flow

```mermaid
flowchart TD
	A[Feed Page Load] --> B[Read current user by session]
	B --> C[Load following list]
	C --> D[Query posts by me and followed users]
	D --> E[Render post cards]

	E --> F{Interaction}
	F --> G[Create post]
	F --> H[Comment]
	F --> I[Like post or comment]
	F --> J[Follow user]

	G --> G1[Post::create_post]
	H --> H1[Post::create_post with parent]
	I --> I1[Post::like_post]
	J --> J1[User::follow_user]

	G1 --> K[(posts table)]
	H1 --> K
	I1 --> L[(likes table)]
	J1 --> L

	I1 --> M[(notifications table)]
	J1 --> M
```

## 4. Messaging Flow (Direct and Group)

```mermaid
flowchart TD
	A[Open messages.php] --> B[Validate logged-in user]
	B --> C[Read conversations and groups]
	C --> D{Selected chat type}

	D -->|Direct| E[Load peer thread]
	D -->|Group| F[Load group thread]

	E --> G[Mark direct messages seen]
	G --> H[Render thread]
	F --> H

	H --> I[Submit message form with CSRF]
	I --> J{Has attachment?}
	J -->|Yes| K[upload_message_file]
	J -->|No| L[send text only]

	K --> M[Message::send_attachment or send_group_attachment]
	L --> N[Message::send_message or send_group_message]
	M --> O[(messages tables + uploads/messages)]
	N --> O
	O --> P[Redirect back to active thread]
```

## 5. Notifications and AJAX Polling Flow

```mermaid
flowchart TD
	A[Client polling or action] --> B{Endpoint}
	B -->|ajax.php action=check_messages| C[ajax/messages.ajax.php]
	B -->|ajax.php action=check_posts| D[ajax/posts.ajax.php]
	B -->|ajax.php action=like_post| E[ajax/like.ajax.php]
	B -->|notifications.php| F[Read notifications page data]

	C --> G[(MySQL)]
	D --> G
	E --> G
	F --> G

	G --> H[Return JSON or HTML]
	H --> I[UI updates badges, toasts, lists]
```

## 6. Church Map Flow

```mermaid
flowchart TD
	A[Open church_map.php] --> B[Check session via Login::check_login]
	B --> C[Initialize Leaflet map]
	C --> D[Request browser geolocation]
	D -->|Allowed| E[Set user coordinates]
	D -->|Denied| F[Show location error message]

	E --> G[Fetch churches from Overpass API]
	G --> H[Compute distance with Haversine]
	H --> I[Sort and render markers and list]

	I --> J[User searches place]
	J --> K[Nominatim geocoding]
	K --> L[Reload nearby churches for new coordinates]
```

