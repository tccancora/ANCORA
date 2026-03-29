const botao = document.getElementById('botao');
const texto = document.getElementById('texto');

// Adiciona um evento de clique
botao.addEventListener('click', () => {
    texto.innerText = "O botão foi clicado! 🚀";
    texto.style.color = "green";
    alert("Você interagiu com a página!");
});