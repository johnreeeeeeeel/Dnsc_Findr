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

// Fix back/forward cache issue
window.addEventListener("pageshow", function (event) {
    hideLoading();
});

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





// Display admin/user details in modal
function viewUserDetails(
    id,
    username,
    fullname,
    sex,
    dob,
    usertype,
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
    document.getElementById('vu_usertype').innerText = usertype;
    document.getElementById('vu_userrole').innerText = userrole;
    document.getElementById('vu_institute').innerText = institute;
    document.getElementById('vu_program').innerText = program;
    document.getElementById('vu_email').innerText = email;
}

function viewAdminDetails(
    id,
    username,
    email,
    usertype
) {
    document.getElementById('va_id').innerText = id;
    document.getElementById('va_username').innerText = username;
    document.getElementById('va_email').innerText = email;
    document.getElementById('va_usertype').innerText = usertype;
}