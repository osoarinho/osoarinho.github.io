# Especificação Técnica de Requisitos - Soarinho: Hub do Ecossistema

## 1. Introdução
Este documento detalha os requisitos técnicos e funcionais do projeto **Soarinho - Artista Tecnológico**. O objetivo é fornecer um guia compreensível para desenvolvedores, designers e equipe de QA, garantindo que a implementação final atenda aos padrões de excelência técnica e estética da marca.

---

## 2. Escopo do Sistema
O sistema consiste em uma **Single Page Application (SPA)** estática, projetada para alta performance e impacto visual. Atua como um hub central para serviços de Música, Locução, Audiovisual, Desenvolvimento Web e Suporte de TI.

---

## 3. Requisitos Funcionais (RF)

### RF01: Navegação e Hub de Serviços
- **Descrição**: O usuário deve navegar entre as seções de serviço de forma fluida.
- **Critérios de Aceite**:
  - Âncoras de menu devem utilizar *smooth scroll*.
  - O header deve alterar sua opacidade e altura ao rolar a página (>50px).
  - O menu mobile (sidebar) deve ser acessível via ícone hambúrguer e fechável via overlay ou botão de fechamento.

### RF02: Portfólio Interativo (Web)
- **Descrição**: Exibição de projetos web com simulação de visualização real.
- **Critérios de Aceite**:
  - Cards de portfólio web devem possuir o efeito "Scrolling Screen" (a imagem do site rola internamente ao passar o mouse).
  - Deve permitir filtragem por categorias (Landing Pages, Institucionais, etc.) sem recarregamento de página.

### RF03: Demonstrações de Voz (Audio Engine)
- **Descrição**: Modal interativo para reprodução de amostras de locução e dublagem.
- **Critérios de Aceite**:
  - Deve possuir um engine de áudio customizado (não nativo visualmente).
  - **Sincronização**: Ao iniciar um áudio, qualquer outro áudio em reprodução deve ser pausado automaticamente.
  - Feedback visual de progresso (barra de progresso funcional e contadores de tempo).
  - O modal deve fechar ao clicar fora, no botão "X" ou na tecla `ESC`.

### RF04: Motor de Temas (Dual Theme)
- **Descrição**: Alternância dinâmica entre Modo Dark e Modo Light.
- **Critérios de Aceite**:
  - A preferência do usuário deve ser persistida via `localStorage`.
  - A transição entre temas deve ser suave (CSS transitions).
  - Ícones de alternância (Sol/Lua) devem atualizar conforme o estado.

---

## 4. Requisitos Não-Funcionais (RNF)

### RNF01: Performance e Otimização
- **Lighthouse**: O site deve buscar pontuação >90 em Performance e SEO.
- **Assets**: Imagens devem utilizar formatos modernos (WebP/AVIF) quando possível e possuir `lazy loading` implementado.

### RNF02: Responsividade (Breakpoints)
- **Desktop**: 1280px+
- **Tablet**: 768px a 1024px
- **Mobile**: <768px (Prioridade para interações touch e botões flutuantes maiores).

### RNF03: SEO e Metadados
- Implementação de **JSON-LD (Schema.org)** para entidade `Person` e `Service`.
- Meta tags completas para Open Graph (Facebook/LinkedIn) e Twitter Cards.

---

## 5. Especificações de UI/UX (Design System)

### 5.1 Tokens de Design
- **Tipografia**: 
  - Primária: `Exo 2` (300 a 800 weight).
  - Mono/Tech: `JetBrains Mono` ou `Fira Code`.
- **Cores (Dark Mode)**:
  - Background: `#050505`.
  - Glass Surface: `rgba(23, 23, 23, 0.3)` com blur de 12px.
  - Primary Glow: White com opacidade variável.

### 5.2 Animações e Micro-interações
- **Reveal on Scroll**: Elementos devem surgir suavemente (fade-in-up) usando `IntersectionObserver`.
- **Parallax**: Efeito sutil de profundidade em imagens de destaque e fundo do Hero.
- **Micro-interações**: Efeito de `glow` em botões de conversão e `float` em elementos de branding.

---

## 6. Plano de QA e Casos de Teste

| ID | Cenário de Teste | Resultado Esperado |
|:---|:---|:---|
| CT01 | Clique no link de serviço no Header | Scroll suave até a seção correspondente. |
| CT02 | Hover em card de portfólio web | A imagem interna do "device" deve rolar até o final. |
| CT03 | Play em áudio "Personagens" e depois em "Comercial" | O primeiro áudio para e o segundo inicia imediatamente. |
| CT04 | Troca de tema Dark -> Light | Cores de fundo e texto alteram; ícone muda para Sol; estado persiste após refresh. |
| CT05 | Envio de formulário vazio | Campos obrigatórios devem mostrar validação nativa do navegador. |
| CT06 | Visualização em iPhone/Android | Menu hambúrguer funcional; Botão de WhatsApp fixo no canto inferior. |
| CT07 | Pressionar 'ESC' com modal de áudio aberto | O modal fecha e todos os áudios param. |

---

## 7. Arquitetura Técnica
- **Frontend**: HTML5 Semântico / Tailwind CSS (Custom Config).
- **Core Engine**: Vanilla JavaScript (ES6+).
- **External Dependencies**: Font Awesome 6, Google Fonts API.
- **Deployment Target**: GitHub com Web Hook em Hostinger.
