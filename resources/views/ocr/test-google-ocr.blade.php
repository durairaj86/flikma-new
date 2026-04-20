<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Google OCR Extractor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Upload PDF or Image for Google OCR Extraction</h5>
        </div>
        <div class="card-body">
            <form id="ocrForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" name="file" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Upload & Extract</button>
            </form>
            <div class="mt-4">
                <h6>Extracted JSON:</h6>
                <pre id="output" class="bg-dark text-white p-3 rounded" style="height: 400px; overflow:auto;"></pre>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $('#ocrForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $('#output').text('Processing...');
        $.ajax({
            url: '{{ route("test-google-ocr.upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#output').text(JSON.stringify(res, null, 4));
            },
            error: function(err) {
                $('#output').text('Error: ' + err.responseText);
            }
        });
    });
</script>
</body>
</html>
