<?php

include("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['mybook_userid']);
$USER = $user_data;

$user_class = new User();
$message_class = new Message();

$myid = (int)$user_data['userid'];
$active_userid = 0;
$active_groupid = 0;

function upload_message_file($myid)
{
    $file_field = 'chat_file';
    if (!isset($_FILES[$file_field]) || empty($_FILES[$file_field]['name'])) {
        $file_field = 'chat_file_upload';
        if (!isset($_FILES[$file_field]) || empty($_FILES[$file_field]['name'])) {
            return false;
        }
    }

    if ($_FILES[$file_field]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $max_size = 50 * 1024 * 1024;
    if ($_FILES[$file_field]['size'] > $max_size) {
        return false;
    }

    $folder = "uploads/messages/" . $myid . "/";
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $original_name = basename($_FILES[$file_field]['name']);
    $ext = pathinfo($original_name, PATHINFO_EXTENSION);
    $safe_ext = $ext ? ("." . preg_replace('/[^a-zA-Z0-9]/', '', $ext)) : "";
    $filename = $folder . uniqid("msg_", true) . $safe_ext;

    if (!move_uploaded_file($_FILES[$file_field]['tmp_name'], $filename)) {
        return false;
    }

    return array(
        'path' => $filename,
        'name' => $original_name,
        'mime' => $_FILES[$file_field]['type'] ?? ''
    );
}

if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $active_userid = (int)$_GET['user'];
}
if (isset($_GET['group']) && is_numeric($_GET['group'])) {
    $active_groupid = (int)$_GET['group'];
    $active_userid = 0;
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    if (!csrf_validate_request()) {
        http_response_code(403);
        die("Invalid request token.");
    }

    if (isset($_POST['create_group']) && !empty($_POST['group_name'])) {
        $group_name = trim($_POST['group_name']);
        $members = isset($_POST['members']) && is_array($_POST['members']) ? $_POST['members'] : array();
        $groupid = $message_class->create_group($myid, $group_name, $members);
        if ($groupid) {
            header("Location: messages.php?group=" . (int)$groupid);
            die;
        }
    }

    if (isset($_POST['receiver']) && is_numeric($_POST['receiver'])) {
        $receiver = (int)$_POST['receiver'];
        $message_text = isset($_POST['message']) ? $_POST['message'] : "";
        $upload = upload_message_file($myid);

        if ($upload !== false) {
            $message_class->send_attachment($myid, $receiver, $message_text, $upload['path'], $upload['name'], $upload['mime']);
        } elseif (trim($message_text) !== "") {
            $message_class->send_message($myid, $receiver, $message_text);
        }

        header("Location: messages.php?user=" . $receiver);
        die;
    }

    if (isset($_POST['group_id']) && is_numeric($_POST['group_id'])) {
        $group_id = (int)$_POST['group_id'];
        $message_text = isset($_POST['message']) ? $_POST['message'] : "";
        $upload = upload_message_file($myid);

        if ($upload !== false) {
            $message_class->send_group_attachment($myid, $group_id, $message_text, $upload['path'], $upload['name'], $upload['mime']);
        } elseif (trim($message_text) !== "") {
            $message_class->send_group_message($myid, $group_id, $message_text);
        }

        header("Location: messages.php?group=" . $group_id);
        die;
    }
}

$conversations = $message_class->get_conversations($myid);
if (!is_array($conversations)) {
    $conversations = array();
}

$groups = $message_class->get_groups_for_user($myid);
if (!is_array($groups)) {
    $groups = array();
}

if ($active_userid <= 0 && $active_groupid <= 0 && count($conversations) > 0) {
    $active_userid = (int)$conversations[0]['peer_id'];
}

$active_user = false;
if ($active_userid > 0) {
    $active_user = $user_class->get_user($active_userid);
    if (!is_array($active_user) || $active_userid === $myid) {
        $active_user = false;
        $active_userid = 0;
    }
}

$active_group = false;
if ($active_groupid > 0) {
    foreach ($groups as $g) {
        if ((int)$g['groupid'] === $active_groupid) {
            $active_group = $g;
            break;
        }
    }
    if (!$active_group) {
        $active_groupid = 0;
    }
}

if ($active_userid > 0) {
    $message_class->mark_seen($myid, $active_userid);
}

$thread = array();
if ($active_userid > 0) {
    $thread = $message_class->get_thread($myid, $active_userid, 200);
} elseif ($active_groupid > 0) {
    $thread = $message_class->get_group_thread($active_groupid, 200);
}
if (!is_array($thread)) {
    $thread = array();
}

$following = $user_class->get_following($myid, "user");
if (!is_array($following)) {
    $following = array();
}

$followers = $user_class->get_followers($myid, "user");
if (!is_array($followers)) {
    $followers = array();
}

$group_candidates = array();
foreach (array_merge($following, $followers) as $rel) {
    $uid = isset($rel['userid']) ? (int)$rel['userid'] : 0;
    if ($uid > 0 && $uid !== $myid) {
        $group_candidates[$uid] = $uid;
    }
}

$call_room = "hopespring_" . $myid;
if ($active_userid > 0) {
    $pair = array($myid, $active_userid);
    sort($pair);
    $call_room = "hopespring_dm_" . $pair[0] . "_" . $pair[1];
} elseif ($active_groupid > 0) {
    $call_room = "hopespring_group_" . $active_groupid;
}
$call_url = "https://meet.jit.si/" . $call_room;

$bible_verses = array(
    array('text' => 'For I know the plans I have for you, declares the Lord, plans for welfare and not for evil, to give you a future and a hope.', 'ref' => 'Jeremiah 29:11'),
    array('text' => 'I can do all things through him who strengthens me.', 'ref' => 'Philippians 4:13'),
    array('text' => 'Trust in the Lord with all your heart, and do not lean on your own understanding.', 'ref' => 'Proverbs 3:5'),
    array('text' => 'Be strong and courageous. Do not fear, for the Lord your God is with you wherever you go.', 'ref' => 'Joshua 1:9')
);
$random_verse = $bible_verses[array_rand($bible_verses)];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages | HopeSpring</title>
</head>
<body>

<?php include("app/partials/header.php"); ?>

<div class="page-wrapper" style="max-width:1080px;">
    <div class="chat-layout card">
        <aside class="chat-sidebar">
            <div class="chat-sidebar-head">
                <h3>Messages</h3>
                <button type="button" class="btn btn-outline btn-sm" id="openGroupModal">Create Group</button>
            </div>

            <?php if (count($groups) > 0): ?>
                <div class="chat-section-title">Groups</div>
                <div class="chat-convo-list" style="margin-bottom:12px;">
                    <?php foreach ($groups as $g): ?>
                        <a href="messages.php?group=<?php echo (int)$g['groupid']; ?>" class="chat-convo-item <?php echo ($active_groupid == (int)$g['groupid']) ? 'active' : ''; ?>">
                            <div class="chat-group-dot">#</div>
                            <div class="chat-convo-main">
                                <div class="chat-convo-top"><strong><?php echo htmlspecialchars($g['group_name']); ?></strong></div>
                                <div class="chat-convo-last">Group chat</div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($conversations) > 0): ?>
                <div class="chat-section-title">Direct Messages</div>
                <div class="chat-convo-list">
                    <?php foreach ($conversations as $conv): ?>
                        <?php
                        $peer_id = (int)$conv['peer_id'];
                        $peer = $user_class->get_user($peer_id);
                        if (!is_array($peer)) {
                            continue;
                        }

                        $peer_img = ($peer['gender'] === "Female") ? "images/user_female.jpg" : "images/user_male.jpg";
                        if (!empty($peer['profile_image']) && file_exists($peer['profile_image'])) {
                            $img = new Image();
                            $peer_img = $img->get_thumb_profile($peer['profile_image']);
                        }

                        $is_active = ($active_userid === $peer_id);
                        $last_text = trim($conv['message']);
                        if ($last_text === "" && !empty($conv['file_name'])) {
                            $last_text = "Attachment: " . $conv['file_name'];
                        }
                        $last_text = substr($last_text, 0, 45);
                        ?>
                        <a href="messages.php?user=<?php echo $peer_id; ?>" class="chat-convo-item <?php echo $is_active ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($peer_img); ?>" class="chat-convo-avatar" alt="">
                            <div class="chat-convo-main">
                                <div class="chat-convo-top">
                                    <strong><?php echo htmlspecialchars($peer['first_name'] . " " . $peer['last_name']); ?></strong>
                                    <span><?php echo date("M j", strtotime($conv['date'])); ?></span>
                                </div>
                                <div class="chat-convo-last"><?php echo htmlspecialchars($last_text); ?></div>
                            </div>
                            <?php if ((int)$conv['unread_count'] > 0): ?>
                                <span class="chat-unread"><?php echo (int)$conv['unread_count']; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="margin-top:12px;">No direct conversations yet.</div>
            <?php endif; ?>
        </aside>

        <section class="chat-main">
            <?php if (is_array($active_user) || is_array($active_group)): ?>
                <?php
                $head_name = "";
                $head_sub = "";
                $head_img = "images/user_male.jpg";

                if (is_array($active_user)) {
                    $head_name = $active_user['first_name'] . " " . $active_user['last_name'];
                    $head_sub = "@" . $active_user['tag_name'];
                    $head_img = ($active_user['gender'] === "Female") ? "images/user_female.jpg" : "images/user_male.jpg";
                    if (!empty($active_user['profile_image']) && file_exists($active_user['profile_image'])) {
                        $img = new Image();
                        $head_img = $img->get_thumb_profile($active_user['profile_image']);
                    }
                } else {
                    $head_name = $active_group['group_name'];
                    $head_sub = "Group chat";
                }
                ?>

                <div class="chat-head">
                    <?php if (is_array($active_user)): ?>
                        <img src="<?php echo htmlspecialchars($head_img); ?>" class="chat-head-avatar" alt="">
                    <?php else: ?>
                        <div class="chat-group-dot large">#</div>
                    <?php endif; ?>
                    <div>
                        <strong><?php echo htmlspecialchars($head_name); ?></strong>
                        <div class="chat-head-sub"><?php echo htmlspecialchars($head_sub); ?></div>
                    </div>
                    <div class="chat-head-actions">
                        <a href="<?php echo htmlspecialchars($call_url); ?>" target="_blank" class="btn btn-outline btn-sm">Call</a>
                        <a href="<?php echo htmlspecialchars($call_url); ?>#config.startWithVideoMuted=false" target="_blank" class="btn btn-outline btn-sm">Video</a>
                    </div>
                </div>

                <div class="chat-thread" id="chatThread">
                    <?php if (count($thread) > 0): ?>
                        <?php foreach ($thread as $msg): ?>
                            <?php
                            $mine = ((int)$msg['sender'] === $myid);
                            $sender_info = $user_class->get_user((int)$msg['sender']);
                            $sender_name = is_array($sender_info) ? ($sender_info['first_name'] . " " . $sender_info['last_name']) : "Unknown";
                            ?>
                            <div class="chat-row <?php echo $mine ? 'me' : 'them'; ?>">
                                <div class="chat-bubble <?php echo $mine ? 'me' : 'them'; ?>">
                                    <?php if ($active_groupid > 0): ?>
                                        <div class="chat-sender-name"><?php echo htmlspecialchars($sender_name); ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($msg['message'])): ?>
                                        <div class="chat-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($msg['file_path']) && file_exists($msg['file_path'])): ?>
                                        <?php if (!empty($msg['mime_type']) && strpos($msg['mime_type'], 'image/') === 0): ?>
                                            <a href="<?php echo htmlspecialchars($msg['file_path']); ?>" target="_blank">
                                                <img src="<?php echo htmlspecialchars($msg['file_path']); ?>" class="chat-attachment-image" alt="image">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($msg['file_path']); ?>" target="_blank" class="chat-file-link">
                                                Attachment: <?php echo htmlspecialchars($msg['file_name'] ?: 'Download file'); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <div class="chat-time"><?php echo date("M j, g:i a", strtotime($msg['date'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">No messages yet. Start chatting.</div>
                    <?php endif; ?>
                </div>

                <form method="post" class="chat-form" enctype="multipart/form-data" id="messageForm">
                    <?php echo csrf_input(); ?>
                    <?php if ($active_groupid > 0): ?>
                        <input type="hidden" name="group_id" value="<?php echo (int)$active_groupid; ?>">
                    <?php else: ?>
                        <input type="hidden" name="receiver" value="<?php echo (int)$active_userid; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="chat_file" id="chatFileInput">
                    <div class="chat-form-container">
                        <div class="chat-form-inputs">
                            <textarea name="message" class="form-control" placeholder="Type your message..." id="messageInput"></textarea>
                            <?php if (!empty($active_userid) || !empty($active_groupid)): ?>
                                <div class="chat-file-preview" id="filePreview" style="display:none;">
                                    <span id="fileName"></span>
                                    <button type="button" class="btn-icon" id="clearFile">✕</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="chat-form-actions">
                            <button type="button" class="btn btn-grey btn-sm" id="recordBtn" title="Record voice note">
                                 🎤</button>
                            <label class="btn btn-grey btn-sm" style="cursor:pointer;margin:0;">
                                📎
                                <input type="file" id="fileInput" name="chat_file_upload" style="display:none;" accept="image/*,.pdf,.doc,.docx,.txt,.zip,.mp3,.wav,.ogg">
                            </label>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </div>
                </form>
                <div class="voice-recorder-modal" id="voiceRecorderModal">
                    <div class="voice-recorder-box">
                        <div class="voice-record-status" id="voiceStatus">Ready to record</div>
                        <div class="voice-record-timer" id="voiceTimer">00:00</div>
                        <div class="voice-record-controls">
                            <button type="button" class="btn btn-danger" id="startRecordBtn">Start Recording</button>
                            <button type="button" class="btn btn-warning" id="stopRecordBtn" style="display:none;">Stop Recording</button>
                        </div>
                        <div class="voice-playback" id="voicePlayback" style="display:none;">
                            <audio id="voicePreview" controls style="width: 100%; margin-bottom: 12px;"></audio>
                            <div style="display:flex;gap:10px;justify-content:flex-end;">
                                <button type="button" class="btn btn-grey" id="discardVoiceBtn">Discard</button>
                                <button type="button" class="btn btn-primary" id="sendVoiceBtn">Send Note</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state chat-empty-panel">
                    <div class="verse-card">
                        <div class="verse-title">Verse of the day</div>
                        <p class="verse-text">"<?php echo htmlspecialchars($random_verse['text']); ?>"</p>
                        <div class="verse-ref"><?php echo htmlspecialchars($random_verse['ref']); ?></div>
                        <div class="verse-hint">Refresh the page for another random verse.</div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<div class="modal-overlay" id="groupModal">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-body">
            <h3 style="margin-bottom:10px;">Create Group</h3>
            <form method="post">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="create_group" value="1">
                <div class="form-group">
                    <label>Group Name</label>
                    <input type="text" class="form-control" name="group_name" placeholder="e.g. Worship Team" required>
                </div>
                <div class="form-group">
                    <label>Add Members</label>
                    <div class="group-member-list">
                        <?php if (count($group_candidates) > 0): ?>
                            <?php foreach ($group_candidates as $candidate_id): ?>
                                <?php $fr = $user_class->get_user($candidate_id); if (!is_array($fr)) { continue; } ?>
                                <label class="group-member-item">
                                    <input type="checkbox" name="members[]" value="<?php echo (int)$fr['userid']; ?>">
                                    <span><?php echo htmlspecialchars($fr['first_name'] . ' ' . $fr['last_name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted">Follow users or get followed to add members.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" class="btn btn-grey" data-close="#groupModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="notificationContainer" style="position:fixed;top:80px;right:16px;z-index:999;display:flex;flex-direction:column;gap:10px;max-width:380px;\"></div>

<script>
(function () {
    const thread = document.getElementById("chatThread");
    const openGroupModalBtn = document.getElementById("openGroupModal");
    const groupModal = document.getElementById("groupModal");
    const messageForm = document.getElementById("messageForm");
    const fileInput = document.getElementById("fileInput");
    const chatFileInput = document.getElementById("chatFileInput");
    const filePreview = document.getElementById("filePreview");
    const clearFileBtn = document.getElementById("clearFile");
    const voiceRecorderModal = document.getElementById("voiceRecorderModal");
    const recordBtn = document.getElementById("recordBtn");
    const startRecordBtn = document.getElementById("startRecordBtn");
    const stopRecordBtn = document.getElementById("stopRecordBtn");
    const discardVoiceBtn = document.getElementById("discardVoiceBtn");
    const sendVoiceBtn = document.getElementById("sendVoiceBtn");
    const voiceStatus = document.getElementById("voiceStatus");
    const voiceTimer = document.getElementById("voiceTimer");
    const voicePlayback = document.getElementById("voicePlayback");
    const voicePreview = document.getElementById("voicePreview");

    let mediaRecorder = null;
    let recordedChunks = [];
    let recordingStartTime = 0;
    let timerInterval = null;
    let selectedFile = null;

    // Scroll to latest message
    if (thread) {
        thread.scrollTop = thread.scrollHeight;
    }

    // Group modal toggle
    if (openGroupModalBtn && groupModal) {
        openGroupModalBtn.addEventListener("click", function () {
            groupModal.classList.add("open");
        });
    }

    document.querySelectorAll("[data-close]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const sel = btn.getAttribute("data-close");
            const modal = document.querySelector(sel);
            if (modal) {
                modal.classList.remove("open");
            }
        });
    });

    if (groupModal) {
        groupModal.addEventListener("click", function (e) {
            if (e.target === groupModal) {
                groupModal.classList.remove("open");
            }
        });
    }

    // File upload handling
    if (fileInput) {
        fileInput.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                selectedFile = this.files[0];
                showFilePreview();
            }
        });
    }

    function showFilePreview() {
        if (selectedFile && filePreview) {
            const size = (selectedFile.size / 1024).toFixed(1);
            document.getElementById("fileName").textContent = selectedFile.name + " (" + size + "KB)";
            filePreview.style.display = "block";
        }
    }

    if (clearFileBtn) {
        clearFileBtn.addEventListener("click", function () {
            selectedFile = null;
            if (fileInput) fileInput.value = "";
            if (filePreview) filePreview.style.display = "none";
        });
    }

    // Drag and drop file upload
    if (messageForm) {
        messageForm.addEventListener("dragover", function (e) {
            e.preventDefault();
            e.stopPropagation();
            messageForm.classList.add("drag-over");
        });

        messageForm.addEventListener("dragleave", function (e) {
            e.preventDefault();
            e.stopPropagation();
            messageForm.classList.remove("drag-over");
        });

        messageForm.addEventListener("drop", function (e) {
            e.preventDefault();
            e.stopPropagation();
            messageForm.classList.remove("drag-over");

            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                selectedFile = e.dataTransfer.files[0];
                if (fileInput) {
                    const dt = new DataTransfer();
                    dt.items.add(selectedFile);
                    fileInput.files = dt.files;
                }
                showFilePreview();
            }
        });

        messageForm.addEventListener("submit", function (e) {
            if (selectedFile && fileInput) {
                const dt = new DataTransfer();
                dt.items.add(selectedFile);
                fileInput.files = dt.files;
            }
        });
    }

    // Voice recording
    if (recordBtn) {
        recordBtn.addEventListener("click", function () {
            if (!voiceRecorderModal) return;
            voiceRecorderModal.classList.add("open");
            recordedChunks = [];
            voicePlayback.style.display = "none";
            startRecordBtn.style.display = "inline-block";
            stopRecordBtn.style.display = "none";
            voiceStatus.textContent = "Ready to record";
            voiceTimer.textContent = "00:00";
        });
    }

    if (startRecordBtn) {
        startRecordBtn.addEventListener("click", async function () {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                recordedChunks = [];
                recordingStartTime = Date.now();

                mediaRecorder.ondataavailable = function (e) {
                    recordedChunks.push(e.data);
                };

                mediaRecorder.onstop = function () {
                    const blob = new Blob(recordedChunks, { type: "audio/webm" });
                    voicePreview.src = URL.createObjectURL(blob);
                    voicePlayback.style.display = "block";
                    startRecordBtn.style.display = "none";
                    voiceRecorderModal.setAttribute("data-blob", null);
                    voiceRecorderModal.voiceBlob = blob;
                    clearInterval(timerInterval);
                };

                mediaRecorder.start();
                startRecordBtn.style.display = "none";
                stopRecordBtn.style.display = "inline-block";
                voiceStatus.textContent = "Recording...";

                timerInterval = setInterval(function () {
                    const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
                    const min = Math.floor(elapsed / 60);
                    const sec = elapsed % 60;
                    voiceTimer.textContent = (min < 10 ? "0" : "") + min + ":" + (sec < 10 ? "0" : "") + sec;
                }, 100);
            } catch (err) {
                alert("Microphone access denied or not available.");
            }
        });
    }

    if (stopRecordBtn) {
        stopRecordBtn.addEventListener("click", function () {
            if (mediaRecorder) {
                mediaRecorder.stop();
                mediaRecorder.stream.getTracks().forEach(function (t) { t.stop(); });
            }
            stopRecordBtn.style.display = "none";
            voiceStatus.textContent = "Recording stopped. Review below.";
        });
    }

    if (discardVoiceBtn) {
        discardVoiceBtn.addEventListener("click", function () {
            recordedChunks = [];
            voiceRecorderModal.voiceBlob = null;
            voicePlayback.style.display = "none";
            startRecordBtn.style.display = "inline-block";
            voiceStatus.textContent = "Ready to record";
            voiceTimer.textContent = "00:00";
        });
    }

    if (sendVoiceBtn) {
        sendVoiceBtn.addEventListener("click", function () {
            if (!voiceRecorderModal.voiceBlob) {
                alert("No voice recording found.");
                return;
            }

            const formData = new FormData();
            formData.append("chat_file", voiceRecorderModal.voiceBlob, "voice_note_" + Date.now() + ".webm");
            if (messageForm.querySelector("input[name='receiver']")) {
                formData.append("receiver", messageForm.querySelector("input[name='receiver']").value);
            } else if (messageForm.querySelector("input[name='group_id']")) {
                formData.append("group_id", messageForm.querySelector("input[name='group_id']").value);
            }
            formData.append("message", "");

            const xhr = new XMLHttpRequest();
            xhr.onload = function () {
                if (xhr.status === 200) {
                    location.reload();
                } else {
                    alert("Error sending voice note.");
                }
            };
            xhr.onerror = function () {
                alert("Error sending voice note.");
            };
            xhr.open("POST", window.location.href);
            xhr.send(formData);
        });
    }

    // Close voice modal on backdrop click
    if (voiceRecorderModal) {
        voiceRecorderModal.addEventListener("click", function (e) {
            if (e.target === voiceRecorderModal) {
                voiceRecorderModal.classList.remove("open");
            }
        });
    }

    // Message notification system
    let lastCheckTime = Date.now();
    const notificationContainer = document.getElementById("notificationContainer");
    const NOTIFICATION_CHECK_INTERVAL = 3000; // Check every 3 seconds

    function showNotificationToast(title, message, type = "info") {
        if (!notificationContainer) return;

        const toast = document.createElement("div");
        toast.className = "notification-toast notification-" + type;
        toast.innerHTML = `
            <div class="notification-content">
                <strong>${title}</strong>
                <p>${message}</p>
            </div>
            <button class="notification-close" type="button">✕</button>
        `;

        const closeBtn = toast.querySelector(".notification-close");
        const removeToast = function () {
            toast.style.opacity = "0";
            setTimeout(function () {
                toast.remove();
            }, 300);
        };

        closeBtn.addEventListener("click", removeToast);
        notificationContainer.appendChild(toast);

        setTimeout(removeToast, 5000);
    }

    function requestBrowserNotification(title, message, peer_id = null) {
        if ("Notification" in window && Notification.permission === "granted") {
            const notif = new Notification(title, {
                body: message,
                icon: "images/logo.jpg",
                tag: "hopespring_msg"
            });
            notif.onclick = function () {
                window.focus();
                if (peer_id) {
                    window.location.href = "messages.php?user=" + peer_id;
                }
            };
            // Play a subtle notification sound
            try {
                const audio = new Audio("data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQIAAAAAAA==");
                audio.volume = 0.3;
                audio.play().catch(function() {});
            } catch (e) {}
        }
    }

    function checkNewMessages() {
        const xhr = new XMLHttpRequest();
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.total_unread > 0) {
                        // Direct message notifications
                        if (response.direct_messages && response.direct_messages.length > 0) {
                            response.direct_messages.forEach(function (msg) {
                                showNotificationToast(msg.peer_name, "New message from " + msg.peer_name, "message");
                                requestBrowserNotification("New message", msg.peer_name + ": " + msg.last_message.substring(0, 50), msg.peer_id);
                            });
                        }
                        // Group message notifications
                        if (response.group_messages && response.group_messages.length > 0) {
                            response.group_messages.forEach(function (msg) {
                                showNotificationToast(msg.group_name, "New message in " + msg.group_name, "message");
                                requestBrowserNotification("New group message", "Message in " + msg.group_name, null);
                            });
                        }
                        // Update message badge in navbar if it exists
                        updateMessageBadge(response.total_unread);
                    }
                } catch (e) {
                    console.error("Error parsing message check response:", e);
                }
            }
        };
        xhr.onerror = function () {
            console.error("Error checking messages");
        };
        xhr.open("GET", "ajax.php?action=check_messages", true);
        xhr.send();
    }

    function updateMessageBadge(count) {
        const badge = document.querySelector("[data-message-count]");
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = "inline-flex";
            } else {
                badge.style.display = "none";
            }
        }
    }

    let lastShownPostIds = [];

    function checkNewPosts() {
        const xhr = new XMLHttpRequest();
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.total_new_posts > 0 && response.posts && response.posts.length > 0) {
                        response.posts.forEach(function (post) {
                            // Only show notification if we haven't shown it for this post yet
                            if (!lastShownPostIds.includes(post.postid)) {
                                lastShownPostIds.push(post.postid);
                                const postPreview = post.post_preview.length > 45 ? post.post_preview + "..." : post.post_preview;
                                showNotificationToast(post.poster_name + " posted", postPreview, "post");
                                requestBrowserNotification("New post", post.poster_name + " posted: " + postPreview, null);
                            }
                        });
                    }
                } catch (e) {
                    console.error("Error parsing posts check response:", e);
                }
            }
        };
        xhr.onerror = function () {
            console.error("Error checking posts");
        };
        xhr.open("GET", "ajax.php?action=check_posts", true);
        xhr.send();
    }

    // Request notification permission
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
    }

    // Start checking for new messages and posts every 3 seconds
    setInterval(function () {
        checkNewMessages();
        checkNewPosts();
    }, NOTIFICATION_CHECK_INTERVAL);

    // Initial check on page load
    setTimeout(function () {
        checkNewMessages();
        checkNewPosts();
    }, 1000);

})();
</script>

</body>
</html>