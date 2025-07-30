<!DOCTYPE html>
<html>
<head>
    <title>Upload Leads CSV</title>
</head>
<body>
    <h1>Upload Leads CSV</h1>
    <form action="import_csv.php" method="post" enctype="multipart/form-data">
        <input type="file" name="csv_file" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
