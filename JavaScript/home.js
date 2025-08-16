// FUNCIONALIDADE DO MENU MOBILE
// Aguarda o carregamento completo da página
document.addEventListener("DOMContentLoaded", () => {
    // Seleciona elementos do menu mobile
    const mobileToggle = document.querySelector(".mobile-menu-toggle")
    const navLinks = document.querySelector(".nav-links")
  
    // Verifica se o botão existe antes de adicionar evento
    if (mobileToggle) {
      // Adiciona evento de clique no botão hambúrguer
      mobileToggle.addEventListener("click", () => {
        // Alterna classe 'active' no menu (mostra/esconde)
        navLinks.classList.toggle("active")
        // Alterna classe 'active' no botão (animação)
        mobileToggle.classList.toggle("active")
      })
    }
  
    // ROLAGEM SUAVE PARA LINKS ÂNCORA
    // Seleciona todos os links que começam com #
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      // Adiciona evento de clique em cada link
      anchor.addEventListener("click", function (e) {
        e.preventDefault() // Previne comportamento padrão
  
        // Encontra o elemento de destino
        const target = document.querySelector(this.getAttribute("href"))
  
        // Se o elemento existe, rola suavemente até ele
        if (target) {
          target.scrollIntoView({
            behavior: "smooth", // Rolagem suave
            block: "start", // Alinha no topo
          })
        }
      })
    })
  
    // EFEITO DE SCROLL NO HEADER
    // Adiciona evento de rolagem da página
    window.addEventListener("scroll", () => {
      const header = document.querySelector(".header")
  
      // Se rolou mais de 100px
      if (window.scrollY > 100) {
        // Adiciona efeito de vidro fosco
        header.style.background = "rgba(255, 255, 255, 0.95)"
        header.style.backdropFilter = "blur(10px)"
      } else {
        // Remove efeito quando volta ao topo
        header.style.background = "white"
        header.style.backdropFilter = "none"
      }
    })
  
    // ANIMAÇÃO DE RIPPLE NOS BOTÕES
    // Seleciona todos os botões
    document.querySelectorAll("button, .primary-button").forEach((button) => {
      // Adiciona evento de clique
      button.addEventListener("click", function (e) {
        // Cria elemento para efeito ripple
        const ripple = document.createElement("span")
  
        // Calcula posição e tamanho do ripple
        const rect = this.getBoundingClientRect()
        const size = Math.max(rect.width, rect.height)
        const x = e.clientX - rect.left - size / 2
        const y = e.clientY - rect.top - size / 2
  
        // Define propriedades do ripple
        ripple.style.width = ripple.style.height = size + "px"
        ripple.style.left = x + "px"
        ripple.style.top = y + "px"
        ripple.classList.add("ripple")
  
        // Adiciona ripple ao botão
        this.appendChild(ripple)
  
        // Remove ripple após animação
        setTimeout(() => {
          ripple.remove()
        }, 600)
      })
    })
  
    // INTERSECTION OBSERVER PARA ANIMAÇÕES
    // Configurações do observer
    const observerOptions = {
      threshold: 0.1, // Ativa quando 10% está visível
      rootMargin: "0px 0px -50px 0px", // Margem inferior de 50px
    }
  
    // Cria o observer
    const observer = new IntersectionObserver((entries) => {
      // Para cada elemento observado
      entries.forEach((entry) => {
        // Se está visível na tela
        if (entry.isIntersecting) {
          // Torna visível e move para posição original
          entry.target.style.opacity = "1"
          entry.target.style.transform = "translateY(0)"
        }
      })
    }, observerOptions)
  
    // CONFIGURA ELEMENTOS PARA ANIMAÇÃO
    // Seleciona elementos que terão animação de entrada
    document.querySelectorAll(".trust-indicators, .hero-text, .profile-image").forEach((el) => {
      // Define estado inicial (invisível e deslocado)
      el.style.opacity = "0"
      el.style.transform = "translateY(30px)"
      el.style.transition = "opacity 0.6s ease, transform 0.6s ease"
  
      // Adiciona elemento ao observer
      observer.observe(el)
    })
  })
  
  // ADICIONA CSS PARA EFEITO RIPPLE DINAMICAMENTE
  // Cria elemento style
  const style = document.createElement("style")
  
  // Define CSS para animação ripple
  style.textContent = `
      /* Estilo do efeito ripple */
      .ripple {
          position: absolute;                    /* Posicionamento absoluto */
          border-radius: 50%;                   /* Formato circular */
          background: rgba(255, 255, 255, 0.6); /* Cor semi-transparente */
          transform: scale(0);                  /* Começa pequeno */
          animation: ripple-animation 0.6s linear; /* Animação */
          pointer-events: none;                 /* Não interfere em cliques */
      }
      
      /* Animação do ripple */
      @keyframes ripple-animation {
          to {
              transform: scale(4); /* Cresce 4x */
              opacity: 0;         /* Fica transparente */
          }
      }
      
      /* Botões precisam ter overflow hidden para ripple funcionar */
      button, .primary-button {
          position: relative; /* Para posicionamento absoluto do ripple */
          overflow: hidden;   /* Esconde ripple que sai do botão */
      }
  `
  
  // Adiciona o CSS ao head da página
  document.head.appendChild(style)