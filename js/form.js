document.addEventListener('DOMContentLoaded', function() {
    const popupContainer = document.getElementById('popup-container');
    const feedbackForm = document.getElementById('feedback-form');
    const messageArea = document.getElementById('message-area');

    const formFields = ['name', 'email', 'phone', 'comment'];

    function showMessage(message, isError = false) {
        messageArea.textContent = message;
        messageArea.style.color = isError ? 'red' : 'green';
        messageArea.style.display = 'block';
    }

    function restoreFormData() {
        formFields.forEach(field => {
            const value = localStorage.getItem(`feedbackForm_${field}`);
            if (value) {
                document.getElementById(field).value = value;
            }
        });
    }

    function saveFormData() {
        formFields.forEach(field => {
            localStorage.setItem(`feedbackForm_${field}`, document.getElementById(field).value);
        });
    }

    function clearFormData() {
        formFields.forEach(field => {
            localStorage.removeItem(`feedbackForm_${field}`);
            document.getElementById(field).value = '';
        });
    }

    async function submitForm(event) {
        event.preventDefault();

        const formData = new FormData(feedbackForm);

        try {
            const response = await fetch('https://formcarry.com/s/_5AsKNrVFk0', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.status === "success") {
                showMessage('Заявка отправлен Успешно!');
                clearFormData();
            } else {
                showMessage(`Ошибка отправки: ${result.message}`, true);
            }
        } catch (error) {
            showMessage(`Непредвиденная ошибка: ${error}`, true);
            console.log(`Непредвиденная ошибка: ${error}`);
        }
    }


    feedbackForm.addEventListener('submit', submitForm);
    restoreFormData();

    formFields.forEach(field => {
        document.getElementById(field).addEventListener('input', saveFormData);
    });
});
