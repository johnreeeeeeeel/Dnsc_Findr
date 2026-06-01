// Loading screen
function showLoading() {
    document.getElementById("loadingScreen").classList.remove("d-none");
}

function hideLoading() {
    document.getElementById("loadingScreen").classList.add("d-none");
}

document.querySelectorAll("form").forEach(form => {
    form.addEventListener("submit", function () {
        showLoading();
    });
});

window.addEventListener("load", hideLoading);

window.addEventListener("pageshow", function (event) {
    hideLoading();
});

// Alert message timout
setTimeout(() => {
    const alertBox = document.getElementById("messageAlert");

    if (alertBox) {
        alertBox.remove();
    }
}, 4500);

// Remove focus when modal closes
document.addEventListener('hide.bs.modal', function (e) {
    const modal = e.target;

    if (modal.contains(document.activeElement)) {
        document.activeElement.blur();
    }
});

// Dynamic header title
function setTitle(el) {
    document.querySelector(".page-title").textContent = el.dataset.title;
}

// View user details
function viewUserDetails(
    id,
    username,
    fullname,
    sex,
    dob,
    userrole,
    institute,
    program,
    email
) {
    document.getElementById('vu_id').innerText = id;
    document.getElementById('vu_username').innerText = username;
    document.getElementById('vu_fullname').innerText = fullname;
    document.getElementById('vu_sex').innerText = sex;
    document.getElementById('vu_dob').innerText = dob;
    document.getElementById('vu_userrole').innerText = userrole;
    document.getElementById('vu_institute').innerText = institute;
    document.getElementById('vu_program').innerText = program;
    document.getElementById('vu_email').innerText = email;
}

// Edit user details
function updateUserDetails(
    id,
    firstname,
    lastname,
    middlename,
    sex,
    dob,
    usertype,
    userrole,
    institute,
    program,
    email
) {
    document.getElementById('eu_id').value = id;

    document.getElementById('eu_firstname').value = firstname;
    document.getElementById('eu_lastname').value = lastname;
    document.getElementById('eu_middlename').value = middlename ?? '';

    document.getElementById('eu_sex').value = sex;
    document.getElementById('eu_dob').value = dob;

    document.getElementById('eu_usertype').value = usertype;
    document.getElementById('eu_userrole').value = userrole ?? '';
    document.getElementById('eu_institute').value = institute ?? '';
    document.getElementById('eu_program').value = program ?? '';

    document.getElementById('eu_email').value = email;
}

// Delete user 
function setDeleteUser(
    id
) {
    document.getElementById('du_id').value = id;
}