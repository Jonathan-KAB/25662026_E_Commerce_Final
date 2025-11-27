$(document).ready(function() {
    $('#login-form').submit(function(e) {
        e.preventDefault();
        
        var email = $('#email').val();
        var password = $('#password').val();

        if (email == '' || password == '') {
            alert('Please fill in all fields!');
            return;
        }

        console.log('About to send AJAX request'); // Debug

        $.ajax({
            url: '../actions/login_user_action.php',
            method: 'POST',
            dataType: 'json', // Added to parse JSON
            data: {
                email: email,
                password: password
            },
            success: function(response) {
                // console.log('Parsed response:', response); // Debug
                if (response.status === 'success') {
                    alert('Login successful!');
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.href = '../index.php';
                    }
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error occurred'); // Debug
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response:', xhr.responseText);
                alert('Error: ' + error);
            }
        });
    });
});