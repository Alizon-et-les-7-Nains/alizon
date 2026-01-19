const buttonExtract: HTMLInputElement = (document.getElementById('button-extract') as HTMLInputElement);
const inputsExtractwo: NodeListOf<HTMLInputElement> = document.querySelectorAll('form#extraire input:not(#tout):not([type="submit"])');

const inputEpuises: HTMLInputElement = (document.getElementById('epuise') as HTMLInputElement);
const inputFaibles: HTMLInputElement = (document.getElementById('faible') as HTMLInputElement);
const inputStocks: HTMLInputElement = (document.getElementById('stock') as HTMLInputElement);
const inputToutExtract: HTMLInputElement = (document.getElementById('tout') as HTMLInputElement);

function updateButtonState(): void {
    buttonExtract.disabled = !Array.from(inputsExtractwo).some(input => input.checked);
}

function updateToutCheckbox(): void {
    const allChecked = Array.from(inputsExtractwo).every(input => input.checked);
    const someChecked = Array.from(inputsExtractwo).some(input => input.checked);
    inputToutExtract.checked = allChecked && someChecked;
}

function countEpuises(): number {
    let products: number = 0;
    (document.querySelectorAll('main.backoffice-stocks article.epuises div.produit') as NodeListOf<HTMLElement>).forEach((product: HTMLElement) => {
        products++;
    })
    return products;
}
function countFaibles(): number {
    let products: number = 0;
    (document.querySelectorAll('main.backoffice-stocks article.faibles div.produit') as NodeListOf<HTMLElement>).forEach((product: HTMLElement) => {
        products++;
    })
    return products;
}
function countStocks(): number {
    let products: number = 0;
    (document.querySelectorAll('main.backoffice-stocks article.stocks div.produit') as NodeListOf<HTMLElement>).forEach((product: HTMLElement) => {
        products++;
    })
    return products;
}

function updateButton() {
    let products: number = 0;
    if (inputEpuises.checked) products += countEpuises();
    if (inputFaibles.checked) products += countFaibles();
    if (inputStocks.checked) products += countStocks();
    buttonExtract.value = `Extraire ${products.toString()} produits`;
}

inputsExtractwo.forEach((input: HTMLInputElement) => {
    input.addEventListener('input', () => {
        updateToutCheckbox();
        updateButtonState();
        updateButton();
    })
})

inputToutExtract?.addEventListener('input', () => {
    // Mise à jour des checkboxs
    inputsExtractwo.forEach((input: HTMLInputElement) => {
        input.checked = inputToutExtract.checked;
    })
    
    // Mise à joue du bouton
    updateButtonState();
    updateButton();
});