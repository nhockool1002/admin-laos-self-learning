<!DOCTYPE html>
<html>
<head>
    <title>Test CSRF</title>
</head>
<body>
    <h1>Test CSRF</h1>
    <form id="test-form">
        @csrf
        <button type="submit">Test CSRF</button>
    </form>
    
    <script>
    document.getElementById('test-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        try {
            const response = await fetch('/test-csrf', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (response.status === 419) {
                alert('CSRF Error 419');
            } else {
                const data = await response.json();
                alert(data.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });
    </script>
</body>
</html> 