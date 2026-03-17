document.addEventListener('DOMContentLoaded', () => {
    const toastEl = document.getElementById('liveToast');
    const toastBody = toastEl.querySelector('.toast-body');
    const toast = new bootstrap.Toast(toastEl);

    document.getElementById('message-form').addEventListener('submit', (event) => {
        event.preventDefault();

        let name = document.getElementById('name').value;
        let email = document.getElementById('email').value;
        let message = document.getElementById('message').value;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/feedback/send/');
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onload = () => {
            if (xhr.status === 200)
            {
                let data = JSON.parse(xhr.response ?? '[]');
                if (!data.success)
                {
                    toastBody.textContent = data.message;
                    toast.show();
                }
                else
                {
                    toastBody.textContent = data.message;
                    toast.show();
                }
            }
            else
            {
                console.log('Server response: ', xhr.statusText);
            }
        };
        xhr.send(JSON.stringify({
            name: name,
            email: email,
            message: message,
        }));
    });
});