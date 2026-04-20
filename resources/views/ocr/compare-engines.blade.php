<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compare OCR Engines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Compare OCR Engines</h5>
        </div>
        <div class="card-body">
            <form id="ocrForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" name="file" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Upload & Compare</button>
            </form>
            <div class="mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Engine 1 (Free):</h6>
                        <pre id="engine1-output" class="bg-dark text-white p-3 rounded" style="height: 400px; overflow:auto;"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6>Engine 2 (Paid):</h6>
                        <pre id="engine2-output" class="bg-dark text-white p-3 rounded" style="height: 400px; overflow:auto;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $('#ocrForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $('#engine1-output').text('Processing...');
        $('#engine2-output').text('Processing...');
        $.ajax({
            url: '{{ route("ocr.compare-upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $('#engine1-output').text(JSON.stringify(res.engine1, null, 4));
                $('#engine2-output').text(JSON.stringify(res.engine2, null, 4));
            },
            error: function(err) {
                $('#engine1-output').text('Error: ' + err.responseText);
                $('#engine2-output').text('Error: ' + err.responseText);
            }
        });
    });
</script>
</body>
</html>
