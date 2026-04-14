function editar(el){
    const target = el.dataset.target;
    document.getElementById(target + "_inputs").style.display='block';
    document.getElementById('save_btn').style.display='inline-block';
}

function togglePassword(el){
    const inputId = el.dataset.target;
    const input = document.getElementById(inputId);
    if(input.type==='password'){
        input.type='text';
        el.classList.remove('fa-eye'); el.classList.add('fa-eye-slash');
    } else {
        input.type='password';
        el.classList.remove('fa-eye-slash'); el.classList.add('fa-eye');
    }
}

document.querySelectorAll('.editable_input').forEach(inp=>{
    inp.addEventListener('input', ()=>{
        document.getElementById('save_btn').style.display='inline-block';
    });
});