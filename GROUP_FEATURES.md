# Group Chat Features Documentation

## Overview
The Message class now includes comprehensive group chat functionality with member management, invitations, admin controls, and profile customization.

---

## Database Schema

### New Tables & Columns

#### `message_groups` (Enhanced)
```sql
- groupid: BIGINT - Unique group identifier
- group_name: VARCHAR(120) - Group name
- group_profile: VARCHAR(255) - Path to group profile image
- group_description: TEXT - Group description
- created_by: BIGINT - Creator's user ID
- date: DATETIME - Creation date
- updated_at: DATETIME - Last update time
```

#### `message_group_members` (Enhanced)
```sql
- groupid: BIGINT - Group ID
- userid: BIGINT - Member's user ID
- role: VARCHAR(20) - 'admin' or 'member'
- joined_date: DATETIME - When user joined
```

#### `group_invitations` (New)
```sql
- inviteid: BIGINT - Unique invitation ID
- groupid: BIGINT - Group ID
- invited_by: BIGINT - Who sent the invitation
- invited_user: BIGINT - Who was invited
- status: VARCHAR(20) - 'pending', 'accepted', 'rejected'
- message: TEXT - Custom invitation message
- created_at: DATETIME - Invitation date
- responded_at: DATETIME - When user responded
```

---

## API Methods

### Group Creation & Info

#### `create_group($creator, $group_name, $member_ids)`
Create a new group chat.

**Parameters:**
- `$creator` (int): User ID of group creator
- `$group_name` (string): Name of the group (max 120 chars)
- `$member_ids` (array): Array of user IDs to add initially

**Returns:** Group ID (int) or false on failure

**Example:**
```php
$Message = new Message();
$group_id = $Message->create_group(123, "Work Team", [124, 125, 126]);
if ($group_id) {
    echo "Group created: " . $group_id;
}
```

#### `get_group_info($groupid)`
Get detailed group information.

**Parameters:**
- `$groupid` (int): Group ID

**Returns:** Array with group details or false

**Example:**
```php
$group = $Message->get_group_info(789);
echo $group['group_name'];  // "Work Team"
echo $group['group_profile']; // "/uploads/123/group_pic.jpg"
echo $group['group_description']; // "Team discussion group"
```

#### `get_groups_for_user($userid)`
Get all groups a user is member of.

**Parameters:**
- `$userid` (int): User ID

**Returns:** Array of groups with member count

**Example:**
```php
$groups = $Message->get_groups_for_user(123);
foreach ($groups as $group) {
    echo $group['group_name'] . " (" . $group['member_count'] . " members)";
}
```

---

### Group Messaging

#### `send_group_message($sender, $groupid, $message)`
Send a text message to a group.

**Parameters:**
- `$sender` (int): Sender's user ID
- `$groupid` (int): Group ID
- `$message` (string): Message text

**Returns:** true or false

**Example:**
```php
$Message->send_group_message(123, 789, "Hello everyone!");
```

#### `send_group_attachment($sender, $groupid, $message, $file_path, $file_name, $mime_type)`
Send a file/image to a group.

**Parameters:**
- `$sender` (int): Sender's user ID
- `$groupid` (int): Group ID
- `$message` (string): Caption/message
- `$file_path` (string): Path to file
- `$file_name` (string): Original filename
- `$mime_type` (string): MIME type (e.g., 'image/jpeg')

**Returns:** true or false

#### `get_group_thread($groupid, $limit)`
Get messages in a group.

**Parameters:**
- `$groupid` (int): Group ID
- `$limit` (int): Number of messages to retrieve (default: 200)

**Returns:** Array of messages

---

### Group Profile & Settings

#### `update_group_name($groupid, $group_name, $userid)`
Change group name (admin only).

**Parameters:**
- `$groupid` (int): Group ID
- `$group_name` (string): New name
- `$userid` (int): User making change (must be admin)

**Returns:** true or false

**Example:**
```php
$success = $Message->update_group_name(789, "New Team Name", 123);
```

#### `update_group_profile($groupid, $profile_path, $userid)`
Change group profile image (admin only).

**Parameters:**
- `$groupid` (int): Group ID
- `$profile_path` (string): Path to image file
- `$userid` (int): User making change (must be admin)

**Returns:** true or false

**Example:**
```php
$Message->update_group_profile(789, "/uploads/123/group_pic.jpg", 123);
```

#### `update_group_description($groupid, $description, $userid)`
Update group description (admin only).

**Parameters:**
- `$groupid` (int): Group ID
- `$description` (string): Group description
- `$userid` (int): User making change (must be admin)

**Returns:** true or false

**Example:**
```php
$Message->update_group_description(789, "A group for team collaboration", 123);
```

---

### Invitations

#### `send_group_invitation($groupid, $invited_user, $invited_by, $message)`
Send a group invitation to a user.

**Parameters:**
- `$groupid` (int): Group ID
- `$invited_user` (int): User ID to invite
- `$invited_by` (int): User ID sending invitation
- `$message` (string): Optional custom message

**Returns:** Invitation ID or false

**Example:**
```php
$inv_id = $Message->send_group_invitation(789, 127, 123, "Join our team!");
```

#### `get_pending_invitations($userid)`
Get all pending invitations for a user.

**Parameters:**
- `$userid` (int): User ID

**Returns:** Array of pending invitations with group details

**Example:**
```php
$invitations = $Message->get_pending_invitations(123);
foreach ($invitations as $invite) {
    echo $invite['group_name'] . " invited by " . $invite['first_name'];
}
```

#### `accept_invitation($inviteid, $userid)`
Accept a group invitation.

**Parameters:**
- `$inviteid` (int): Invitation ID
- `$userid` (int): User accepting invitation

**Returns:** true or false

**Example:**
```php
if ($Message->accept_invitation(456, 123)) {
    echo "You joined the group!";
}
```

#### `reject_invitation($inviteid, $userid)`
Reject a group invitation.

**Parameters:**
- `$inviteid` (int): Invitation ID
- `$userid` (int): User rejecting invitation

**Returns:** true or false

---

### Member Management

#### `get_group_members($groupid)`
Get all members of a group with their details.

**Parameters:**
- `$groupid` (int): Group ID

**Returns:** Array of members with name and role

**Example:**
```php
$members = $Message->get_group_members(789);
foreach ($members as $member) {
    echo $member['first_name'] . " - Role: " . $member['role'];
}
```

#### `remove_group_member($groupid, $userid, $removed_by)`
Remove a member from group (admin only).

**Parameters:**
- `$groupid` (int): Group ID
- `$userid` (int): Member to remove
- `$removed_by` (int): Admin removing member

**Returns:** true or false

**Example:**
```php
$Message->remove_group_member(789, 127, 123);
```

#### `leave_group($groupid, $userid)`
User leaves a group.

**Parameters:**
- `$groupid` (int): Group ID
- `$userid` (int): User leaving

**Returns:** true or false (cannot leave if creator)

**Example:**
```php
if ($Message->leave_group(789, 123)) {
    echo "You left the group";
}
```

#### `get_group_member_count($groupid)`
Get number of members in a group.

**Parameters:**
- `$groupid` (int): Group ID

**Returns:** Member count (int)

---

### Admin Controls

#### `promote_to_admin($groupid, $userid, $promoted_by)`
Promote a member to admin (admin only).

**Parameters:**
- `$groupid` (int): Group ID
- `$userid` (int): Member to promote
- `$promoted_by` (int): Admin performing promotion

**Returns:** true or false

**Example:**
```php
$Message->promote_to_admin(789, 125, 123);
```

#### `demote_to_member($groupid, $userid, $demoted_by)`
Demote an admin to member (admin only).

**Parameters:**
- `$groupid` (int): Group ID
- `$userid` (int): Admin to demote
- `$demoted_by` (int): Admin performing demotion

**Returns:** true or false

#### `is_group_admin($groupid, $userid)`
Check if a user is an admin.

**Parameters:**
- `$groupid` (int): Group ID
- `$userid` (int): User ID

**Returns:** true or false

**Example:**
```php
if ($Message->is_group_admin(789, 123)) {
    echo "User is admin";
}
```

#### `is_group_member($groupid, $userid)`
Check if a user is a member.

**Parameters:**
- `$groupid` (int): Group ID
- `$userid` (int): User ID

**Returns:** true or false

#### `delete_group($groupid, $userid)`
Delete a group (creator only).

**Parameters:**
- `$groupid` (int): Group ID
- `$userid` (int): User requesting deletion (must be creator)

**Returns:** true or false

**Example:**
```php
if ($Message->delete_group(789, 123)) {
    echo "Group deleted";
}
```

---

## Complete Usage Example

```php
<?php
include('classes/connect.php');
include('classes/message.php');

$Message = new Message();

// Create a new group
$group_id = $Message->create_group(123, "Project Alpha", [124, 125]);

// Update group profile
$Message->update_group_profile($group_id, "/uploads/123/alpha.jpg", 123);

// Update description
$Message->update_group_description($group_id, "Discussion for Project Alpha", 123);

// Send a message
$Message->send_group_message(123, $group_id, "Welcome to the group!");

// Invite another user
$Message->send_group_invitation($group_id, 126, 123, "Join our project!");

// Get pending invitations for user 126
$invites = $Message->get_pending_invitations(126);

// Accept invitation
if (!empty($invites)) {
    $Message->accept_invitation($invites[0]['inviteid'], 126);
}

// Get all members
$members = $Message->get_group_members($group_id);

// Promote a member to admin
$Message->promote_to_admin($group_id, 124, 123);

// Get group info
$group_info = $Message->get_group_info($group_id);

// Get messages
$messages = $Message->get_group_thread($group_id);

?>
```

---

## Permission Model

- **Creator**: Always has admin privileges, can delete group
- **Admin**: Can invite/remove members, change group name/profile/description, promote/demote others
- **Member**: Can send messages, leave group, accept/reject invitations

---

## Error Handling

All methods return `false` on failure. Check return values:

```php
$result = $Message->update_group_name(789, "New Name", 123);
if (!$result) {
    echo "Failed to update group name";
    // Reasons: invalid group, unauthorized user, invalid name
}
```

---

## Security Features

✅ All SQL queries use prepared statements (SQL injection proof)  
✅ Role-based access control (admin-only operations)  
✅ Integer type casting for IDs  
✅ String trimming and validation  
✅ Member verification before operations  

---

## Version History

- **v1.0** (April 1, 2026): Initial implementation
  - Group creation and messaging
  - Member management
  - Invitations system
  - Admin controls
  - Group customization
