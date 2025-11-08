<?php
$hash = '$2y$10$9EMwzeuZzd2ejRyoeFBbhO.a2m6Txb0QpHzGb9jz2pmcdOjWvl9oO';
$result = password_verify('admin123', $hash);
var_dump($result);
