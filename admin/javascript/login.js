// --Login--
document.querySelector('.admin-login-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch('../php/login.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                window.location.href = '../html/dashboard.html';
            } else {
                document.getElementById('loginError').textContent = result.message;
            }

        } catch (error) {
            document.getElementById('loginError').textContent = 'Something went wrong. Try again.';
        }
    });