import $ from 'jquery';

$(document).ready(function() {
    const parametersData = document.getElementById('parameters-data');
    const form = $('#idForm3DS');

    if (parametersData && form.length) {
        try {
            const parameters = JSON.parse(parametersData.textContent.trim());
            parameters.forEach(campo => {
                const input = `<input type="hidden" name="${campo.name}" value="${campo.value}">`;
                form.append(input);
            });
            form.submit();
        } catch (error) {
            console.error('Invalid JSON in #parameters-data:', error);
        }
    }
});
