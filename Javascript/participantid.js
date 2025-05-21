function participantID(input) {
    let value = input.value.replace(/\D/g, '').slice(0, 8);
        if (value.length > 2) {
    input.value = value.slice(0, 2) + '-' + value.slice(2);
        } else {
            input.value = value;
        }
}

function age(input) {
    let value = input.value.replace(/\D/g, ''); 
    if (value.length > 2) {
        value = value.slice(0, 2); 
    }
    input.value = value;
}
