<?php
session_start();
session_unset(); // সব সেশন ভ্যারিয়েবল ক্লিয়ার করে
session_destroy(); // সেশন পুরোপুরি ধ্বংস করে
header("Location: ../index.php"); // লগআউটের পর হোমপেইজে রিডিরেক্ট করে
exit();
?>
