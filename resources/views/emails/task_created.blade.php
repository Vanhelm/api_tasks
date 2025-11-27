<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Новая задача</title>
</head>
<body>
<h1>Создана новая задача {{ $task->title }}</h1>
<p><strong>Название:</strong> {{ $task->title }}</p>
<p><strong>Описание:</strong> {{ $task->description }}</p>
<p><strong>Ответственный:</strong> {{ $task->user->name }}</p>
</body>
</html>
