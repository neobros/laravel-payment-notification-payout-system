<!-- resources/views/upload.blade.php -->
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Upload Payments CSV</title>
  <style> body{font-family:sans-serif;max-width:600px;margin:40px auto} </style>
</head>
<body>
  <h2>Upload Payments CSV</h2>
  <form id="f" enctype="multipart/form-data">
    <input type="file" name="file" accept=".csv" required>
    <button type="submit">Upload</button>
  </form>
  <pre id="out"></pre>
  <script>
    const f = document.getElementById('f');
    const out = document.getElementById('out');
    f.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(f);
      const res = await fetch('/api/payments/upload', { method:'POST', body: fd });
      out.textContent = JSON.stringify(await res.json(), null, 2);
    });
  </script>
</body>
</html>
