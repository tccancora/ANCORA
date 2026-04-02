// 1. Alternar entre os itens da Sidebar (Menu)
const menuItems = document.querySelectorAll('.sidebar nav a');

menuItems.forEach(item => {
    item.addEventListener('click', (e) => {
        // Remove a classe 'active' de todos
        menuItems.forEach(i => i.classList.remove('active'));
        // Adiciona apenas no que foi clicado
        e.target.classList.add('active');
    });
});

// 2. Lógica do botão "+ Criar Evento"
const btnCriar = document.querySelector('.btn-criar');

btnCriar.addEventListener('click', () => {
    const nomeEvento = prompt("Qual o nome do novo evento?");
    if (nomeEvento) {
        alert(`Evento "${nomeEvento}" criado com sucesso! (Simulação)`);
        // Aqui você poderia adicionar o código para criar um novo card na tela
    }
});

// 3. Simulação de contador dinâmico (Stats)
// Imagina que esses números venham de um banco de dados
const stats = {
    total: 6,
    proximos: 4,
    andamento: 1,
    concluidos: 1
};

console.log(`Você tem ${stats.proximos} eventos chegando!`);