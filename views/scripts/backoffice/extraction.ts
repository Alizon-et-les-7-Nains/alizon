const buttonExtract: HTMLInputElement = (document.getElementById('button-extract') as HTMLInputElement);
const inputsExtractwo: NodeListOf<HTMLInputElement> = document.querySelectorAll('form#extraire input:not(#tout):not([type="submit"])');
const inputToutExtract: HTMLInputElement = (document.getElementById('tout') as HTMLInputElement);

function updateButtonState(): void {
    buttonExtract.disabled = !Array.from(inputsExtractwo).some(input => input.checked);
}

function updateToutCheckbox(): void {
    const allChecked = Array.from(inputsExtractwo).every(input => input.checked);
    const someChecked = Array.from(inputsExtractwo).some(input => input.checked);
    inputToutExtract.checked = allChecked && someChecked;
}

inputsExtractwo.forEach((input: HTMLInputElement) => {
    input.addEventListener('input', () => {
        updateToutCheckbox();
        updateButtonState();
    })
})

inputToutExtract.addEventListener('input', () => {
    inputsExtractwo.forEach((input: HTMLInputElement) => {
        input.checked = inputToutExtract.checked;
    })
    updateButtonState();
})