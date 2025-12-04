<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../back-end/src/db-config.php';

// ---------------------
// VERIFICA LOGIN
// ---------------------
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
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
<title>Feed | Nexus</title>
<link rel="icon" href="../images/logo_botinho_pagina.png" type="image/x-icon">

<link rel="stylesheet" href="../css/menu.css"> 
<link rel="stylesheet" href="../css/header.css">
<link rel="stylesheet" href="../css/feed.css"> 

<style>
/* FORMULÁRIO DE POSTAGEM */
.post-form {
    background: #161618;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
}
.post-form textarea {
    width: 100%;
    height: 90px;
    resize: none;
    border: none;
    padding: 12px;
    border-radius: 8px;
    background: #1f1f21;
    color: #fff;
    margin-bottom: 12px;
}
.post-form input[type="file"] {
    margin-bottom: 15px;
    color: #fff;
}
.post-form button {
    background: linear-gradient(135deg, #7d3cff, #a770ff);
    border: none;
    padding: 10px 16px;
    border-radius: 10px;
    color: #fff;
    cursor: pointer;
    font-weight: 600;
}
.post-img {
    width: 100%;
    border-radius: 12px;
    margin-top: 12px;
}
#preview-img { margin-top:8px; max-height:200px; object-fit:cover; }
.comments-area { margin-top:10px; padding:8px; background:#0f0f11; border-radius:8px; }
.comment-item { padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.03); }
.comment-item .small { font-size:12px; color:#aaa; }
.btn-like[data-liked="1"] { color: #ffb86b; }
</style>

<link rel="stylesheet" 
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="dashboard-layout"> 

    <!-- SIDEBAR -->
    <div class="sidebar-placeholder">
        <?php include "menu-sidebar.php"; ?>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main-content">

        <!-- HEADER -->
        <div class="header-placeholder">
            <?php include "main-header.php"; ?>
        </div>

        <div class="explorar">Explorar Feed</div>

        <!-- FORMULÁRIO DE POSTAGEM -->
        <form action="../../back-end/src/postar.php" 
              method="POST" 
              enctype="multipart/form-data" 
              class="post-form">

            <textarea name="texto" placeholder="O que você está pensando?" maxlength="500"></textarea>
            <input type="file" name="imagem" accept="image/*">
            <button type="submit">Publicar</button>
        </form>

        <!-- FILTROS DO FEED -->
        <div class="feed-filtros">
            <a href="feed.php" class="filtro-btn active-filtro"><i class="fas fa-fire"></i> Destaques</a>
            <a href="favoritos.php" class="filtro-btn"><i class="fas fa-star"></i> Favoritos</a>
        </div>

        <!-- CONTAINER PARA POSTS (AJAX) -->
        <div id="feed-container" class="feed-container"></div>

    </div>
</div>

<script>
// =======================
// CONFIGURAÇÕES AJAX
// =======================
const FETCH_URL = '../../back-end/src/fetch_posts.php';
const POST_URL = '../../back-end/src/postar.php';
const LIKE_URL = '../../back-end/src/like.php';
const COMMENT_URL = '../../back-end/src/comment.php';
const GET_COMMENTS_URL = '../../back-end/src/get_comments.php';

const feedContainer = document.getElementById('feed-container');
const postForm = document.querySelector('.post-form');
const textoInput = postForm.querySelector('textarea[name="texto"]');
const fileInput = postForm.querySelector('input[name="imagem"]');

// =======================
// Pré-visualização de imagem
// =======================
fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        let prev = document.getElementById('preview-img');
        if (!prev) {
            prev = document.createElement('img');
            prev.id = 'preview-img';
            prev.style.maxWidth = '100%';
            prev.style.borderRadius = '12px';
            postForm.appendChild(prev);
        }
        prev.src = reader.result;
    };
    reader.readAsDataURL(file);
});

// =======================
// Publicar post via AJAX
// =======================
postForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const form = new FormData(postForm);

    // Loading button
    const btn = postForm.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Publicando...';

    fetch(POST_URL, {
        method: 'POST',
        body: form,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    }).then(r => r.json()).then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        textoInput.value = '';
        fileInput.value = '';
        const prev = document.getElementById('preview-img');
        if (prev) prev.remove();
        loadPosts();
    }).catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Erro ao publicar');
    });
});

// =======================
// Carregar posts
// =======================
function loadPosts() {
    fetch(FETCH_URL, {credentials:'same-origin'})
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        feedContainer.innerHTML = '';
        data.posts.forEach(p => feedContainer.appendChild(buildPostElement(p)));
    });
}

// =======================
// Criar elemento de post
// =======================
function buildPostElement(p) {
    const el = document.createElement('div');
    el.className = 'postagem';
    el.dataset.postId = p.id;

    // cabeçalho
    const head = document.createElement('div');
    head.className = 'cabecalho-post';
    head.innerHTML = `
        <div class="perfil-status-container">
            <div class="imagem-perfil">
                <img src="${p.foto_perfil ? '../uploads/' + p.foto_perfil : '../images/default-avatar.png'}" 
                     alt="avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
            </div>
        </div>
        <div class="informacao-post">
            <div class="nome-usuario verificado">${escapeHtml(p.nome)}</div>
            <div class="arroba">@${escapeHtml(p.handle)}</div>
        </div>
    `;
    el.appendChild(head);

    // conteúdo
    const content = document.createElement('div');
    content.className = 'conteudo-post';
    content.innerHTML = `<p>${nl2br(escapeHtml(p.texto || ''))}</p>`;
    if (p.imagem) {
        const img = document.createElement('img');
        img.className = 'post-img';
        img.src = '../uploads/' + p.imagem;
        content.appendChild(img);
    }
    el.appendChild(content);

    // interação
    const inter = document.createElement('div');
    inter.className = 'interacao-post';
    inter.innerHTML = `
        <div class="icones-interacao">
            <button class="btn-like interacao-item" data-liked="${p.liked_by_me>0?1:0}">
                <i class="fas fa-thumbs-up"></i> <span class="like-count">${p.likes_count}</span>
            </button>
            <button class="btn-share interacao-item"><i class="fas fa-share-alt"></i> Compartilhar</button>
            <button class="btn-toggle-comments interacao-item">
                <i class="fas fa-comment-dots"></i> <span class="comments-count">${p.comments_count}</span> Comentários
            </button>
        </div>
        <div class="data-post">${p.criado_em || 'Agora mesmo'}</div>
    `;
    el.appendChild(inter);

    // comentários
    const commentsArea = document.createElement('div');
    commentsArea.className = 'comments-area';
    commentsArea.style.display = 'none';
    commentsArea.innerHTML = `
        <div class="comments-list"></div>
        <form class="comment-form">
            <input type="text" name="conteudo" placeholder="Escreva um comentário..." required>
            <button type="submit">Comentar</button>
        </form>
    `;
    el.appendChild(commentsArea);

    // eventos
    inter.querySelector('.btn-like').addEventListener('click', () => toggleLike(p.id, el));
    inter.querySelector('.btn-toggle-comments').addEventListener('click', () => {
        commentsArea.style.display = commentsArea.style.display==='none' ? 'block' : 'none';
        if (commentsArea.style.display==='block') loadComments(p.id, el);
    });

    commentsArea.querySelector('.comment-form').addEventListener('submit', function(e){
        e.preventDefault();
        const conteudo = this.conteudo.value.trim();
        if (!conteudo) return;
        fetch(COMMENT_URL, {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'post_id='+encodeURIComponent(p.id)+'&conteudo='+encodeURIComponent(conteudo)
        }).then(r=>r.json()).then(res=>{
            if (res.success){
                this.conteudo.value='';
                appendComment(el,res.comment);
                const cc = el.querySelector('.comments-count');
                cc.textContent = parseInt(cc.textContent||'0')+1;
            } else alert('Erro: '+(res.msg||''));
        });
    });

    return el;
}

// =======================
// Carregar comentários
// =======================
function loadComments(postId, postEl) {
    fetch(GET_COMMENTS_URL+'?post_id='+postId)
    .then(r=>r.json())
    .then(data=>{
        const list = postEl.querySelector('.comments-list');
        list.innerHTML='';
        if (!data.success) return;
        data.comments.forEach(c=>appendComment(postEl,c));
    });
}
function appendComment(postEl,c){
    const list = postEl.querySelector('.comments-list');
    const div = document.createElement('div');
    div.className='comment-item';
    div.innerHTML=`<strong>${escapeHtml(c.nome)}</strong> <span class="comment-text">${nl2br(escapeHtml(c.conteudo))}</span> <div class="small">${c.criado_em}</div>`;
    list.prepend(div);
}

// =======================
// Toggle like
// =======================
function toggleLike(postId, postEl){
    fetch(LIKE_URL,{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'post_id='+encodeURIComponent(postId)
    }).then(r=>r.json()).then(res=>{
        if(!res.success) return;
        const btn = postEl.querySelector('.btn-like');
        const countSpan = postEl.querySelector('.like-count');
        let count = parseInt(countSpan.textContent||'0');
        if(res.action==='liked'){ count++; btn.dataset.liked='1'; }
        else { count=Math.max(0,count-1); btn.dataset.liked='0'; }
        countSpan.textContent=count;
    });
}

// =======================
// Helpers
// =======================
function nl2br(str){return str.replace(/\n/g,'<br>');}
function escapeHtml(unsafe){return unsafe?unsafe.replace(/[&<"'>]/g,function(m){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m];}):'';}

// =======================
// Inicializar
// =======================
loadPosts();
</script>

</body>
</html>
