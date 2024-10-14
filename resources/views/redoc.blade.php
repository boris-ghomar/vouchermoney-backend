<!DOCTYPE html>
<html>
<head>
    <title>API Documentation</title>
    <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
</head>
<body>
<redoc spec-url="{{ url('api-docs.json') }}"></redoc>
<script>
    Redoc.init('{{ url('api-docs.json') }}', {}, document.querySelector('redoc'));
</script>
</body>
</html>
