$(document).ready(function() {
    $('#register-form').submit(function(e) {
        e.preventDefault();

        name = $('#name').val();
        email = $('#email').val();
        password = $('#password').val();
        confirm_password = $('#confirm_password').val();
        phone_number = $('#phone_number').val();
        country = $('#country').val();  // Added this
        city = $('#city').val();        // Added this
        role = $('input[name="role"]:checked').val();

        if (name == '' || email == '' || password == '' || phone_number == '' || country == '' || city == '') {  // Added country and city check
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill in all fields!',
            });

            return;
        } else if (password.length < 6 || !password.match(/[a-z]/) || !password.match(/[A-Z]/) || !password.match(/[0-9]/)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Password must be at least 6 characters long and contain at least one lowercase letter, one uppercase letter, and one number!',
            });

            return;
        } else if (confirm_password == '' || confirm_password !== password) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please make sure the Confirm Password matches the Password',
            });

            return;
        }

        $.ajax({
            url: '../actions/register_user_action.php',
            type: 'POST',
            data: {
                name: name,
                email: email,
                password: password,
                phone_number: phone_number,
                country: country,    // Added this
                city: city,          // Added this
                role: role
            },
            success: function(response) {
                console.log('Success response:', response); // Debug line
                
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message,
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error Details:'); // Debug lines
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response Text:', xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred! Check console for details.',
                });
            }
        });
    });
});