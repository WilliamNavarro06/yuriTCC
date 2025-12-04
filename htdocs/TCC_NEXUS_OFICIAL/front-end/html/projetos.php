<?php
session_start();
include '../../back-end/src/db-config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Projetos de Estudo | Nexus</title>
<link rel="icon" href="../images/logo_botinho_pagina.png" type="image/x-icon">
<link rel="stylesheet" href="../css/menu.css">
<link rel="stylesheet" href="../css/header.css">
<link rel="stylesheet" href="../css/projetos.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <div class="sidebar-placeholder">
        <?php include "menu-sidebar.php"; ?>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main-content">
        <div class="page-content-scrollable">

            <h2 class="page-title">Meus Projetos & Grupos de Estudo</h2>

            <div class="projetos-actions-bar">
                <input type="text" placeholder="Buscar projetos..." class="input-search" id="search-projetos">
                <button id="btn-novo-projeto" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Iniciar Projeto
                </button>
            </div>

            <div class="projetos-filtros-detalhes">
                <select class="select-filtro" id="filtro-tipo">
                    <option value="">Tipo: Todos</option>
                    <option value="tcc">TCC / Monografia</option>
                    <option value="extensao">Extensão / Pesquisa</option>
                    <option value="disciplina">Trabalho de Disciplina</option>
                </select>
                <select class="select-filtro" id="filtro-status">
                    <option value="">Status: Todos</option>
                    <option value="ativos">Em Andamento</option>
                    <option value="aprovado">Aprovado / Concluído</option>
                    <option value="rascunho">Rascunho / Planejamento</option>
                </select>
            </div>

            <div class="projeto-content-flex">
                <section class="lista-projetos" id="lista-projetos"></section>
                <section class="projeto-feed-section" id="feed-projetos">
                    <h3 class="section-subtitle">Feed de Atividades Recentes</h3>
                    <div id="feed-atividades"></div>
                </section>
            </div>

        </div>
    </div>
</div>

<!-- Modal de Novo Projeto -->
<div id="modal-projeto" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);justify-content:center;align-items:center;">
    <div style="background:#161618;padding:20px;border-radius:12px;width:400px;position:relative;">
        <h3>Criar Novo Projeto</h3>
        <form id="form-novo-projeto">
            <input type="text" name="titulo" placeholder="Título do projeto" required style="width:100%;margin-bottom:10px;">
            <textarea name="descricao" placeholder="Descrição" style="width:100%;margin-bottom:10px;"></textarea>
            <select name="tipo" required style="width:100%;margin-bottom:10px;">
                <option value="tcc">TCC / Monografia</option>
                <option value="extensao">Extensão / Pesquisa</option>
                <option value="disciplina">Trabalho de Disciplina</option>
            </select>
            <select name="status" required style="width:100%;margin-bottom:10px;">
                <option value="ativos">Em Andamento</option>
                <option value="aprovado">Aprovado / Concluído</option>
                <option value="rascunho">Rascunho / Planejamento</option>
            </select>
            <input type="text" name="curso" placeholder="Curso" style="width:100%;margin-bottom:10px;">
            <button type="submit" class="btn btn-primary">Criar Projeto</button>
        </form>
        <button onclick="document.getElementById('modal-projeto').style.display='none'" style="position:absolute;top:10px;right:10px;">X</button>
    </div>
</div>

<script>
const listaProjetos = document.getElementById('lista-projetos');
const feedAtividades = document.getElementById('feed-atividades');

// Carrega projetos do banco
function carregarProjetos(){
    const tipo = document.getElementById('filtro-tipo').value;
    const status = document.getElementById('filtro-status').value;
    const search = document.getElementById('search-projetos').value;

    fetch(`../../back-end/src/listar_projetos.php?tipo=${tipo}&status=${status}&search=${search}`)
    .then(r=>r.json())
    .then(data=>{
        listaProjetos.innerHTML='';
        if(!data.success) return;
        data.projetos.forEach(p=>{
            const card = document.createElement('article');
            card.className = 'card-projeto tipo-' + p.tipo + ' status-' + p.status;
            card.innerHTML = `
                <div class="projeto-info">
                    <span class="projeto-tipo status-tag tipo-${p.tipo}">${p.tipo.toUpperCase()}</span>
                    <h3><a href="#" class="projeto-link-title">${p.titulo}</a></h3>
                    <p class="projeto-descricao">${p.descricao}</p>
                    <div class="projeto-metadata">
                        <span class="curso"><i class="fas fa-graduation-cap"></i> ${p.curso}</span>
                        <span class="equipe"><i class="fas fa-users"></i> Líder: ${p.lider_nome}</span>
                    </div>
                </div>
                <div class="projeto-acoes">
                    <a href="#" class="btn-call-to-action"><i class="fas fa-cogs"></i> Gerenciar</a>
                </div>
            `;
            listaProjetos.appendChild(card);
        });
    });
}

// Carrega feed de atividades
function carregarFeed(){
    fetch('../../back-end/src/feed_projetos.php')
    .then(r=>r.json())
    .then(data=>{
        feedAtividades.innerHTML='';
        if(!data.success) return;
        data.atividades.forEach(f=>{
            const div = document.createElement('div');
            div.className='feed-item';
            div.innerHTML = `<span class="feed-icon"><i class="fas fa-file-upload"></i></span><p>${f.acao}</p><span class="feed-time">${f.criado_em}</span>`;
            feedAtividades.appendChild(div);
        });
    });
}

// Abrir modal
document.getElementById('btn-novo-projeto').addEventListener('click',()=> {
    document.getElementById('modal-projeto').style.display='flex';
});

// Criar novo projeto via AJAX
document.getElementById('form-novo-projeto').addEventListener('submit', function(e){
    e.preventDefault();
    const form = new FormData(this);
    fetch('../../back-end/src/criar_projeto.php',{
        method:'POST',
        body:form
    }).then(r=>r.json()).then(data=>{
        if(data.success){
            alert('Projeto criado com sucesso!');
            document.getElementById('modal-projeto').style.display='none';
            this.reset();
            carregarProjetos();
        } else {
            alert('Erro ao criar projeto');
        }
    });
});

// filtros e pesquisa
document.getElementById('filtro-tipo').addEventListener('change',carregarProjetos);
document.getElementById('filtro-status').addEventListener('change',carregarProjetos);
document.getElementById('search-projetos').addEventListener('input',carregarProjetos);

// inicial
carregarProjetos();
carregarFeed();
</script>

</body>
</html>
