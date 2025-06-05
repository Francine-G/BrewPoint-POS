document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.container');
    const signupBtn = document.querySelector('.toggle-left .toggle-signup-btn');
    const loginBtn = document.querySelector('.toggle-right .toggle-login-btn');

    signupBtn.addEventListener('click', () => {
        console.log('Sign Up clicked');
        container.classList.add('active');
    });

    loginBtn.addEventListener('click', () => {
        container.classList.remove('active');
    });
});