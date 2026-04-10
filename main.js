/* SOARINHO.COM - REFACTORED JS */

document.addEventListener("DOMContentLoaded", () => {

  
          tailwind.config = {
              theme: {
                  extend: {
                      fontFamily: {
                          'exo': ['Exo 2', 'sans-serif'],
                          'tech': ['JetBrains Mono', 'Fira Code', 'Courier New', 'monospace'],
                      },
                      colors: {
                          dark: {
                              50: '#fcfcfc',
                              100: '#f0f0f0',
                              200: '#d1d1d1',
                              300: '#a3a3a3',
                              400: '#737373',
                              500: '#525252',
                              600: '#404040',
                              700: '#262626',
                              800: '#171717',
                              900: '#0a0a0a',
                              950: '#050505',
                          },
                          code: {
                              green: '#6A9955',
                              blue: '#569CD6',
                              yellow: '#DCDCAA',
                              orange: '#CE9178',
                              purple: '#C586C0'
                          }
                      },
                      animation: {
                          'float': 'float 6s ease-in-out infinite',
                          'glow': 'glow 3s ease-in-out infinite alternate',
                          'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                          'slide-in': 'slideIn 0.8s ease-out forwards',
                          'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                          'shimmer': 'shimmer 2.5s linear infinite',
                          'gradient': 'gradient 8s ease infinite',
                          'gradient-shift': 'gradient-shift 12s ease infinite',
                          'ticker': 'ticker 50s linear infinite',
                      },
                      keyframes: {
                          float: {
                              '0%, 100%': { transform: 'translateY(0)' },
                              '50%': { transform: 'translateY(-15px)' },
                          },
                          glow: {
                              '0%': { 'box-shadow': '0 0 10px rgba(255, 255, 255, 0.1)' },
                              '100%': { 'box-shadow': '0 0 30px rgba(255, 255, 255, 0.3)' },
                          },
                          slideIn: {
                              '0%': { opacity: '0', transform: 'translateY(30px)' },
                              '100%': { opacity: '1', transform: 'translateY(0)' },
                          },
                          fadeInUp: {
                              '0%': { opacity: '0', transform: 'translateY(40px)' },
                              '100%': { opacity: '1', transform: 'translateY(0)' },
                          },
                          shimmer: {
                              '0%': { backgroundPosition: '-200% 0' },
                              '100%': { backgroundPosition: '200% 0' },
                          },
                          gradient: {
                              '0%, 100%': { backgroundPosition: '0% 50%' },
                              '50%': { backgroundPosition: '100% 50%' },
                          },
                          'gradient-shift': {
                              '0%, 100%': { opacity: '0.4', transform: 'scale(1)' },
                              '50%': { opacity: '0.8', transform: 'scale(1.05)' },
                          },
                          'ticker': {
                              '0%': { transform: 'translate3d(0, 0, 0)' },
                              '100%': { transform: 'translate3d(-50%, 0, 0)' },
                          }
                      }
                  }
              }
          }
      
  
          // Back to Top functionality
          const backToTopBtn = document.getElementById('backToTop');
  
          // Show/hide button based on scroll position
          window.addEventListener('scroll', function () {
              if (window.scrollY > 300) {
                  backToTopBtn.classList.add('visible');
              } else {
                  backToTopBtn.classList.remove('visible');
              }
          });
  
          // Scroll to top when clicked
          backToTopBtn.addEventListener('click', function () {
              window.scrollTo({
                  top: 0,
                  behavior: 'smooth'
              });
          });
      
  
      {
          "@context": "https://schema.org",
          "@type": "Person",
          "name": "Lucas Soares dos Santos",
          "alternateName": "Soarinho",
          "jobTitle": "Artista Tecnológico",
          "description": "Unindo Arte e Tecnologia para Criar Experiências que Conectam e Encantam",
          "url": "https://soarinho.com",
          "sameAs": [
              "https://instagram.com/osoarinho",
              "https://www.youtube.com/@osoarinho",
              "https://open.spotify.com/intl-pt/artist/5f2rUX3TWTtIjOiUuscI6e",
              "https://www.linkedin.com/in/lucas-soares-dos-santos-42925919b/",
              "https://github.com/osoarinho"
          ],
          "knowsAbout": ["Música", "Locução", "Dublagem", "Edição de Vídeo", "Desenvolvimento Web", "Suporte Técnico", "TI"],
          "address": {
              "@type": "PostalAddress",
              "addressLocality": "Angra dos Reis",
              "addressRegion": "RJ",
              "addressCountry": "BR"
          }
      }
      
  
          // Header scroll effect
          window.addEventListener('scroll', function () {
              const header = document.getElementById('header');
              const container = header.querySelector('.container > div');
              if (window.scrollY > 50) {
                  header.classList.remove('py-4', 'md:py-6');
                  header.classList.add('py-2');
                  if (container) {
                      container.classList.add('header-scrolled');
                      container.classList.remove('bg-white/5', 'border-white/10');
                  }
              } else {
                  header.classList.add('py-4', 'md:py-6');
                  header.classList.remove('py-2');
                  if (container) {
                      container.classList.remove('header-scrolled');
                      container.classList.add('bg-white/5', 'border-white/10');
                  }
              }
          });
  
          // Mobile sidebar toggle
          const mobileMenuBtn = document.getElementById('mobile-menu-btn');
          const mobileSidebar = document.getElementById('mobile-sidebar');
          const mobileSidebarOverlay = document.getElementById('mobile-sidebar-overlay');
          const mobileSidebarClose = document.getElementById('mobile-sidebar-close');
  
          function openSidebar() {
              mobileSidebar.classList.add('active');
              mobileSidebarOverlay.classList.add('active');
              document.body.style.overflow = 'hidden';
          }
  
          function closeSidebar() {
              mobileSidebar.classList.remove('active');
              mobileSidebarOverlay.classList.remove('active');
              document.body.style.overflow = '';
          }
  
          if (mobileMenuBtn) {
              mobileMenuBtn.addEventListener('click', openSidebar);
          }
  
          if (mobileSidebarClose) {
              mobileSidebarClose.addEventListener('click', closeSidebar);
          }
  
          if (mobileSidebarOverlay) {
              mobileSidebarOverlay.addEventListener('click', closeSidebar);
          }
  
          // Close sidebar when clicking on a link
          if (mobileSidebar) {
              const mobileLinks = mobileSidebar.querySelectorAll('a');
              mobileLinks.forEach(link => {
                  link.addEventListener('click', closeSidebar);
              });
          }
  
          // Smooth scroll
          document.querySelectorAll('a[href^="#"]').forEach(anchor => {
              anchor.addEventListener('click', function (e) {
                  e.preventDefault();
                  const target = document.querySelector(this.getAttribute('href'));
                  if (target) {
                      target.scrollIntoView({
                          behavior: 'smooth',
                          block: 'start'
                      });
                  }
              });
          });
  
          // Scroll reveal animation
          const reveals = document.querySelectorAll('.reveal');
  
          // Função para ativar o reveal
          const activateReveal = (el) => {
              el.classList.add('active');
          };
  
          const revealObserver = new IntersectionObserver((entries) => {
              entries.forEach(entry => {
                  if (entry.isIntersecting) {
                      activateReveal(entry.target);
                      revealObserver.unobserve(entry.target);
                  }
              });
          }, {
              threshold: 0.05,
              rootMargin: '0px 0px 80px 0px' // Aumentado para disparar ainda mais cedo
          });
  
          reveals.forEach(reveal => {
              // Se estiver no hero, revela quase imediatamente ou após um pequeno delay fixo
              if (reveal.closest('#hero')) {
                  setTimeout(() => activateReveal(reveal), 100);
              } else {
                  revealObserver.observe(reveal);
              }
          });
  
          // Parallax Effect for Images
          const parallaxImages = document.querySelectorAll('.parallax-image');
  
          if (parallaxImages.length > 0 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
              window.addEventListener('scroll', () => {
                  const scrolled = window.pageYOffset;
  
                  parallaxImages.forEach((img, index) => {
                      const rect = img.getBoundingClientRect();
                      const speed = 0.3 + (index * 0.1);
  
                      if (rect.top < window.innerHeight && rect.bottom > 0) {
                          const yPos = -(scrolled * speed);
                          img.style.transform = `translateY(${yPos}px)`;
                      }
                  });
              });
          }
  
          // Hero Background Parallax
          const heroBgImage = document.getElementById('hero-bg-image');
          if (heroBgImage && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
              window.addEventListener('scroll', () => {
                  const scrolled = window.pageYOffset;
                  const heroSection = document.getElementById('hero');
                  const heroRect = heroSection.getBoundingClientRect();
  
                  if (heroRect.top < window.innerHeight && heroRect.bottom > 0) {
                      heroBgImage.style.transform = `translateY(${scrolled * 0.5}px)`;
                  }
              });
          }
  
          // Theme Switcher
          const themeToggle = document.getElementById('theme-toggle');
          const themeToggleMobile = document.getElementById('theme-toggle-mobile');
          const themeIcon = document.getElementById('theme-icon');
          const themeIconMobile = document.getElementById('theme-icon-mobile');
  
          // Check for saved theme preference or default to dark
          const currentTheme = localStorage.getItem('theme') || 'dark';
  
          // Apply theme on page load
          if (currentTheme === 'light') {
              document.body.classList.add('light-theme');
              if (themeIcon) themeIcon.classList.remove('fa-moon');
              if (themeIcon) themeIcon.classList.add('fa-sun');
              if (themeIconMobile) themeIconMobile.classList.remove('fa-moon');
              if (themeIconMobile) themeIconMobile.classList.add('fa-sun');
          }
  
          // Toggle theme function
          function toggleTheme() {
              document.body.classList.toggle('light-theme');
              const isLight = document.body.classList.contains('light-theme');
  
              // Update icons
              if (themeIcon) {
                  if (isLight) {
                      themeIcon.classList.remove('fa-moon');
                      themeIcon.classList.add('fa-sun');
                  } else {
                      themeIcon.classList.remove('fa-sun');
                      themeIcon.classList.add('fa-moon');
                  }
              }
  
              if (themeIconMobile) {
                  if (isLight) {
                      themeIconMobile.classList.remove('fa-moon');
                      themeIconMobile.classList.add('fa-sun');
                  } else {
                      themeIconMobile.classList.remove('fa-sun');
                      themeIconMobile.classList.add('fa-moon');
                  }
              }
  
              // Save preference
              localStorage.setItem('theme', isLight ? 'light' : 'dark');
          }
  
          // Add event listeners
          if (themeToggle) {
              themeToggle.addEventListener('click', toggleTheme);
          }
          if (themeToggleMobile) {
              themeToggleMobile.addEventListener('click', toggleTheme);
          }
  
          // Portfolio Tabs Functionality
          const portfolioTabButtons = document.querySelectorAll('.portfolio-tab-btn');
          const portfolioTabContents = document.querySelectorAll('.portfolio-tab-content');
  
          portfolioTabButtons.forEach(button => {
              button.addEventListener('click', () => {
                  const targetTab = button.getAttribute('data-tab');
  
                  // Remove active class from all buttons and contents
                  portfolioTabButtons.forEach(btn => btn.classList.remove('active'));
                  portfolioTabContents.forEach(content => content.classList.remove('active'));
  
                  // Add active class to clicked button and corresponding content
                  button.classList.add('active');
                  const targetContent = document.getElementById(`tab-${targetTab}`);
                  if (targetContent) {
                      targetContent.classList.add('active');
                  }
              });
          });
  
          // Voice Demos Modal - Inicialização quando DOM estiver pronto
          function initVoiceDemosModal() {
              const openVoiceDemosModal = document.getElementById('open-voice-demos-modal');
              const closeVoiceDemosModal = document.getElementById('close-voice-demos-modal');
              const voiceDemosModal = document.getElementById('voice-demos-modal');
  
              if (!openVoiceDemosModal || !voiceDemosModal) {
                  console.warn('Elementos do modal de demonstrações não encontrados', {
                      button: !!openVoiceDemosModal,
                      modal: !!voiceDemosModal
                  });
                  return;
              }
  
              console.log('Modal de demonstrações inicializado');
  
              // Abrir modal
              openVoiceDemosModal.addEventListener('click', function (e) {
                  e.preventDefault();
                  e.stopPropagation();
                  console.log('Botão clicado, abrindo modal...');
                  voiceDemosModal.classList.add('active');
                  document.body.style.overflow = 'hidden';
  
                  // Ativar os elementos reveal dentro do modal
                  setTimeout(() => {
                      const reveals = voiceDemosModal.querySelectorAll('.reveal');
                      reveals.forEach(reveal => {
                          reveal.classList.add('active');
                      });
                  }, 100);
  
                  console.log('Modal deve estar visível agora', voiceDemosModal.classList.contains('active'));
              });
  
              // Fechar modal
              function closeModal() {
                  voiceDemosModal.classList.remove('active');
                  document.body.style.overflow = '';
  
                  // Resetar os elementos reveal para que possam aparecer novamente quando o modal abrir
                  const reveals = voiceDemosModal.querySelectorAll('.reveal');
                  reveals.forEach(reveal => {
                      reveal.classList.remove('active');
                  });
  
                  // Pausar todos os áudios ao fechar o modal
                  document.querySelectorAll('#voice-demos-modal .hidden-audio').forEach(audio => {
                      audio.pause();
                      audio.currentTime = 0;
                      const player = audio.closest('.custom-audio-player');
                      if (player) {
                          const playIcon = player.querySelector('.play-pause-btn i');
                          const progressBar = player.querySelector('.progress-bar');
                          const currentTime = player.querySelector('.current-time');
                          if (playIcon) {
                              playIcon.classList.remove('fa-pause');
                              playIcon.classList.add('fa-play');
                          }
                          if (progressBar) progressBar.style.width = '0%';
                          if (currentTime) currentTime.textContent = '0:00';
                      }
                  });
              }
  
              if (closeVoiceDemosModal) {
                  closeVoiceDemosModal.addEventListener('click', closeModal);
              }
  
              // Fechar modal ao clicar no overlay
              voiceDemosModal.addEventListener('click', (e) => {
                  if (e.target === voiceDemosModal) {
                      closeModal();
                  }
              });
  
              // Fechar modal com ESC
              document.addEventListener('keydown', (e) => {
                  if (e.key === 'Escape' && voiceDemosModal.classList.contains('active')) {
                      closeModal();
                  }
              });
          }
  
          // Inicializar quando DOM estiver pronto
          if (document.readyState === 'loading') {
              document.addEventListener('DOMContentLoaded', initVoiceDemosModal);
          } else {
              initVoiceDemosModal();
          }
  
          // Custom Audio Players para o modal
          function initAudioPlayers() {
              const players = document.querySelectorAll('#voice-demos-modal .custom-audio-player');
              if (players.length === 0) {
                  console.warn('Players de áudio não encontrados no modal');
                  return;
              }
  
              players.forEach(player => {
                  const audio = player.querySelector('.hidden-audio');
                  const playPauseBtn = player.querySelector('.play-pause-btn');
                  if (!audio || !playPauseBtn) return;
  
                  const playIcon = playPauseBtn.querySelector('i');
                  const progressBar = player.querySelector('.progress-bar');
                  const progressContainer = player.querySelector('.progress-container');
                  const currentTimeEl = player.querySelector('.current-time');
                  const totalTimeEl = player.querySelector('.total-time');
  
                  // Formatar tempo
                  function formatTime(seconds) {
                      if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
                      const mins = Math.floor(seconds / 60);
                      const secs = Math.floor(seconds % 60);
                      return `${mins}:${secs.toString().padStart(2, '0')}`;
                  }
  
                  // Atualizar tempo total
                  audio.addEventListener('loadedmetadata', () => {
                      if (totalTimeEl) totalTimeEl.textContent = formatTime(audio.duration);
                      audio.volume = 1;
                  });
  
                  // Atualizar progresso
                  audio.addEventListener('timeupdate', () => {
                      if (audio.duration) {
                          const progress = (audio.currentTime / audio.duration) * 100;
                          if (progressBar) progressBar.style.width = progress + '%';
                          if (currentTimeEl) currentTimeEl.textContent = formatTime(audio.currentTime);
                      }
                  });
  
                  // Play/Pause
                  playPauseBtn.addEventListener('click', () => {
                      if (audio.paused) {
                          // Pausar outros players
                          document.querySelectorAll('#voice-demos-modal .hidden-audio').forEach(otherAudio => {
                              if (otherAudio !== audio && !otherAudio.paused) {
                                  otherAudio.pause();
                                  const otherPlayer = otherAudio.closest('.custom-audio-player');
                                  if (otherPlayer) {
                                      const otherIcon = otherPlayer.querySelector('.play-pause-btn i');
                                      if (otherIcon) {
                                          otherIcon.classList.remove('fa-pause');
                                          otherIcon.classList.add('fa-play');
                                      }
                                  }
                              }
                          });
                          audio.play();
                          if (playIcon) {
                              playIcon.classList.remove('fa-play');
                              playIcon.classList.add('fa-pause');
                          }
                      } else {
                          audio.pause();
                          if (playIcon) {
                              playIcon.classList.remove('fa-pause');
                              playIcon.classList.add('fa-play');
                          }
                      }
                  });
  
                  // Clique na barra de progresso
                  if (progressContainer) {
                      progressContainer.addEventListener('click', (e) => {
                          const rect = progressContainer.getBoundingClientRect();
                          const percent = (e.clientX - rect.left) / rect.width;
                          audio.currentTime = percent * audio.duration;
                      });
                  }
  
                  // Quando o áudio termina
                  audio.addEventListener('ended', () => {
                      if (playIcon) {
                          playIcon.classList.remove('fa-pause');
                          playIcon.classList.add('fa-play');
                      }
                      if (progressBar) progressBar.style.width = '0%';
                      if (currentTimeEl) currentTimeEl.textContent = '0:00';
                  });
              });
          }
  
          // Inicializar players quando DOM estiver pronto
          if (document.readyState === 'loading') {
              document.addEventListener('DOMContentLoaded', initAudioPlayers);
          } else {
              initAudioPlayers();
          }
  
          // Centralizar grids com poucos itens (exceto hero section)
          function centerGridsWithFewItems() {
              const grids = document.querySelectorAll('.grid');
              grids.forEach(grid => {
                  // Pular grids dentro da hero section
                  if (grid.closest('#hero')) {
                      return;
                  }
  
                  const items = grid.children;
                  const itemCount = items.length;
  
                  // Remove classe anterior se existir
                  grid.classList.remove('grid-center-few');
  
                  // Se há apenas 1 item
                  if (itemCount === 1) {
                      grid.classList.add('grid-center-few');
                  }
                  // Se há apenas 2 itens em grid de 3 ou mais colunas
                  else if (itemCount === 2) {
                      const isGrid3Cols = grid.classList.contains('lg:grid-cols-3') ||
                          grid.classList.contains('md:grid-cols-2');
                      const isGrid4Cols = grid.classList.contains('lg:grid-cols-4');
                      if (isGrid3Cols || isGrid4Cols) {
                          grid.classList.add('grid-center-few');
                      }
                  }
                  // Se há apenas 3 itens em grid de 4 colunas
                  else if (itemCount === 3) {
                      if (grid.classList.contains('lg:grid-cols-4')) {
                          grid.classList.add('grid-center-few');
                      }
                  }
              });
          }
  
          // Executar quando DOM estiver pronto e após carregar
          if (document.readyState === 'loading') {
              document.addEventListener('DOMContentLoaded', centerGridsWithFewItems);
          } else {
              centerGridsWithFewItems();
          }
  
          // Re-executar após animações de reveal
          setTimeout(centerGridsWithFewItems, 1000);
  
      
  
      function googleTranslateElementInit() {
        new google.translate.TranslateElement({pageLanguage: 'pt', includedLanguages: 'en,es,ja,pt', autoDisplay: false}, 'google_translate_element');
      }
      
  
      document.addEventListener('DOMContentLoaded', () => {
          const langToggleBtn = document.getElementById('lang-toggle-btn');
          const langMenu = document.getElementById('lang-menu');
          
          langToggleBtn.addEventListener('click', (e) => {
              e.stopPropagation();
              langMenu.classList.toggle('hidden');
              langMenu.classList.toggle('flex');
          });
  
          document.addEventListener('click', (e) => {
              if (!langToggleBtn.contains(e.target) && !langMenu.contains(e.target)) {
                  langMenu.classList.add('hidden');
                  langMenu.classList.remove('flex');
              }
          });
  
          document.querySelectorAll('.lang-option').forEach(item => {
              item.addEventListener('click', event => {
                  let lang = event.currentTarget.getAttribute('data-lang');
                  let selectField = document.querySelector('select.goog-te-combo');
                  if(selectField) {
                      selectField.value = lang;
                      selectField.dispatchEvent(new Event('change'));
                  } else if (lang === 'pt') {
                      let iframe = document.querySelector('iframe.goog-te-banner-frame');
                      if(iframe) {
                          let innerDoc = iframe.contentDocument || iframe.contentWindow.document;
                          let btn = innerDoc.getElementById('restore');
                          if(btn) btn.click();
                      } else {
                          document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                          location.reload();
                      }
                  }
                  langMenu.classList.add('hidden');
                  langMenu.classList.remove('flex');
              });
          });
      });
      
  const el_event_bind_1 = document.getElementById("event-bind-1");

  if(el_event_bind_1) {

    el_event_bind_1.addEventListener("error", function(event) {

      this.src='https://soarinho.com/portraits/main.png'

    });

  }

  const el_event_bind_2 = document.getElementById("event-bind-2");

  if(el_event_bind_2) {

    el_event_bind_2.addEventListener("error", function(event) {

      this.onerror=null; this.src='https://soarinho.com/identity/marca-soarinho.png';

    });

  }

});
