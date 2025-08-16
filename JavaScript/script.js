// Dados mockados (simulando um banco de dados)
const posts = [
    {
      id: 1,
      username: "@joaosilva",
      avatar: "https://via.placeholder.com/40",
      content: "Acabei de publicar meu novo projeto no GitHub! #frontend #javascript",
      likes: 24,
      comments: 5,
      time: "2 horas atrás"
    },
    {
      id: 2,
      username: "@mariaoliveira",
      avatar: "https://via.placeholder.com/40",
      content: "Alguém tem dicas para estudar CSS Grid?",
      likes: 12,
      comments: 8,
      time: "4 horas atrás"
    }
  ];
  
  // Função para renderizar posts
  function renderPosts() {
    const postsContainer = document.getElementById('posts');
    postsContainer.innerHTML = '';
  
    posts.forEach(post => {
      const postElement = document.createElement('div');
      postElement.className = 'post';
      postElement.innerHTML = `
        <div class="post-header">
          <img src="${post.avatar}" alt="Avatar" class="post-avatar">
          <span class="post-username">${post.username}</span>
          <span class="post-time">${post.time}</span>
        </div>
        <div class="post-content">
          ${post.content}
        </div>
        <div class="post-actions">
          <span class="post-action">❤️ ${post.likes}</span>
          <span class="post-action">💬 ${post.comments}</span>
          <span class="post-action">↗️ Compartilhar</span>
        </div>
      `;
      postsContainer.appendChild(postElement);
    });
  }
  
  // Postar novo conteúdo
  document.querySelector('.post-button').addEventListener('click', () => {
    const textarea = document.querySelector('.post-creator textarea');
    const content = textarea.value.trim();
  
    if (content) {
      const newPost = {
        id: posts.length + 1,
        username: "@novousuario",
        avatar: "https://via.placeholder.com/40",
        content: content,
        likes: 0,
        comments: 0,
        time: "Agora mesmo"
      };
  
      posts.unshift(newPost); // Adiciona no início do array
      renderPosts();
      textarea.value = '';
    }
  });
  
  // Inicializa o feed
  renderPosts();