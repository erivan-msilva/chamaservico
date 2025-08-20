# 🛠️ ChamaServiço

Um sistema completo para conectar clientes e prestadores de serviços de forma simples e eficiente.

## 📋 Sobre o Projeto

O **ChamaServiço** é uma plataforma web desenvolvida em PHP que facilita a contratação de serviços domésticos e profissionais. O sistema permite que clientes publiquem suas necessidades e prestadores enviem propostas, criando um marketplace local de serviços.

### ✨ Funcionalidades Principais

#### 👤 Para Clientes
- **Cadastro e Login** seguro com validação
- **Gerenciamento de Perfil** com foto e dados pessoais
- **Múltiplos Endereços** com sistema de endereço principal
- **Criar Solicitações** com fotos, descrição detalhada e urgência
- **Receber Propostas** de prestadores qualificados
- **Aceitar/Recusar Propostas** com sistema de notificação
- **Acompanhar Status** do serviço em tempo real

#### 🔧 Para Prestadores
- **Dashboard Completo** com estatísticas e métricas
- **Buscar Serviços** com filtros avançados
- **Enviar Propostas** com valor, prazo e descrição
- **Gerenciar Propostas** enviadas
- **Perfil Profissional** especializado

#### 🎯 Recursos Gerais
- **Interface Responsiva** compatível com dispositivos móveis
- **Upload de Imagens** para solicitações
- **Sistema de Status** para acompanhamento
- **Filtros Inteligentes** por tipo, urgência, localização
- **Segurança CSRF** em todos os formulários
- **Validações** robustas de dados

## 🚀 Tecnologias Utilizadas

### Backend
- **PHP 8.2+** - Linguagem principal
- **MySQL/MariaDB** - Banco de dados
- **PDO** - Camada de abstração de dados
- **Arquitetura MVC** - Padrão de desenvolvimento

### Frontend
- **Bootstrap 5.3** - Framework CSS
- **Bootstrap Icons** - Ícones
- **JavaScript Vanilla** - Interatividade
- **AJAX** - Requisições assíncronas

### Ferramentas
- **XAMPP** - Ambiente de desenvolvimento
- **Composer** - Gerenciador de dependências (futuro)
- **Git** - Controle de versão

## 📦 Instalação

### Pré-requisitos
- XAMPP (Apache + MySQL + PHP 8.2+)
- Navegador web moderno
- Editor de código (VS Code recomendado)

### Passo a Passo

1. **Clone o repositório**
```bash
git clone https://github.com/seu-usuario/chamaservico.git
cd chamaservico
```

2. **Configure o XAMPP**
   - Inicie Apache e MySQL no painel do XAMPP
   - Coloque o projeto na pasta `c:\xampp\htdocs\chamaservico`

3. **Configure o Banco de Dados**
   - Acesse http://localhost/phpmyadmin
   - Crie um banco chamado `bd_servicos`
   - Importe o arquivo `config/bd_servicos.sql`

4. **Configure a Conexão**
   - Edite `core/Database.php` se necessário
   - Verifique as credenciais do banco

5. **Teste a Instalação**
   - Acesse http://localhost:8083/chamaservico
   - Use as contas de teste para validar

### 🔑 Contas de Teste

```
Cliente:
Email: teste@sistema.com
Senha: 123456

Prestador:
Email: contatoerivan.ms@gmail.com
Senha: 123456
```

## 🏗️ Estrutura do Projeto

```
chamaservico/
├── config/                 # Configurações do sistema
│   ├── Database.php        # Conexão com banco
│   ├── session.php         # Gerenciamento de sessões
│   └── bd_servicos.sql     # Script do banco
├── controllers/            # Lógica de negócio
│   ├── AuthController.php  # Autenticação
│   ├── SolicitacaoController.php
│   ├── ClientePerfilController.php
│   ├── PrestadorController.php
│   └── PropostaController.php
├── models/                 # Modelos de dados
│   ├── SolicitacaoServico.php
│   ├── Perfil.php
│   ├── Proposta.php
│   └── Auth.php
├── views/                  # Interface do usuário
│   ├── layouts/           # Templates base
│   ├── auth/              # Login/Registro
│   ├── cliente/           # Área do cliente
│   ├── prestador/         # Área do prestador
│   └── solicitacoes/      # Gerenciar serviços
├── uploads/               # Arquivos enviados
│   ├── perfil/           # Fotos de perfil
│   └── solicitacoes/     # Fotos de serviços
├── core/                  # Classes principais
├── router.php            # Roteamento da aplicação
├── index.php            # Ponto de entrada
└── README.md           # Este arquivo
```

## 🗄️ Banco de Dados

### Principais Tabelas

- **tb_pessoa** - Usuários (clientes e prestadores)
- **tb_endereco** - Endereços dos usuários
- **tb_solicita_servico** - Solicitações de serviços
- **tb_proposta** - Propostas dos prestadores
- **tb_tipo_servico** - Categorias de serviços
- **tb_status_solicitacao** - Status dos serviços
- **tb_imagem_solicitacao** - Fotos das solicitações

### Relacionamentos
- Um usuário pode ter múltiplos endereços
- Uma solicitação pertence a um cliente e um endereço
- Uma proposta conecta prestador e solicitação
- Múltiplas imagens por solicitação

## 🔧 Funcionalidades Técnicas

### Segurança
- ✅ Hash de senhas com `password_hash()`
- ✅ Tokens CSRF em formulários
- ✅ Validação de entrada de dados
- ✅ Proteção contra SQL Injection (PDO)
- ✅ Controle de acesso por tipo de usuário

### Performance
- ✅ Consultas otimizadas com JOINs
- ✅ Lazy loading de imagens
- ✅ Compressão de assets
- ✅ Cache de sessão

### Usabilidade
- ✅ Interface responsiva
- ✅ Feedback visual de ações
- ✅ Estados de loading
- ✅ Mensagens de erro/sucesso
- ✅ Navegação intuitiva

## 🎨 Tipos de Serviços Disponíveis

1. **Limpeza Residencial** - Faxina e organização
2. **Serviços Elétricos** - Instalações e reparos
3. **Encanamento** - Hidráulica em geral
4. **Pintura** - Residencial e comercial
5. **Jardinagem** - Cuidados com plantas
6. **Ar Condicionado** - Instalação e manutenção
7. **Mudanças** - Transporte de móveis
8. **Montagem de Móveis** - Montagem e desmontagem

## 📊 Status de Solicitações

- **🟡 Aguardando Propostas** - Solicitação aberta
- **🔵 Em Análise** - Cliente analisando propostas
- **🟢 Proposta Aceita** - Prestador selecionado
- **🟠 Em Andamento** - Serviço sendo executado
- **✅ Concluído** - Serviço finalizado
- **❌ Cancelado** - Cancelado pelo cliente

## 🔄 Fluxo do Sistema

1. **Cliente** se cadastra e cria perfil
2. **Cliente** adiciona endereços de atendimento
3. **Cliente** cria solicitação com fotos e detalhes
4. **Prestadores** visualizam solicitações disponíveis
5. **Prestadores** enviam propostas com valor e prazo
6. **Cliente** recebe e analisa propostas
7. **Cliente** aceita uma proposta
8. **Sistema** atualiza status e notifica partes
9. **Serviço** é executado e finalizado

## 🤝 Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

### 📝 Convenções de Código

- Use **camelCase** para variáveis PHP
- Use **snake_case** para nomes de tabelas/campos
- Comente código complexo
- Mantenha funções pequenas e focadas
- Valide sempre dados de entrada

## 📞 Contato e Suporte

- **Desenvolvedor:** Erivan Mendes da Silva
- **Email:** contatoerivan.ms@gmail.com
- **GitHub:** [Seu GitHub]
- **LinkedIn:** [Seu LinkedIn]

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 🚀 Próximas Funcionalidades

### Em Desenvolvimento
- [ ] Sistema de avaliações e comentários
- [ ] Chat em tempo real entre cliente e prestador
- [ ] Integração com pagamento online
- [ ] App mobile (React Native)
- [ ] Sistema de notificações push

### Backlog
- [ ] API REST para integrações
- [ ] Sistema de fidelidade
- [ ] Geolocalização avançada
- [ ] Relatórios e analytics
- [ ] Sistema de cupons e descontos

## 🏆 Conquistas

- ✅ Sistema funcional completo
- ✅ Interface responsiva e moderna
- ✅ Segurança implementada
- ✅ Upload de múltiplas imagens
- ✅ Filtros avançados de busca
- ✅ Sistema de propostas robusto

---

**⭐ Se este projeto foi útil para você, considere dar uma estrela no repositório!**

*Desenvolvido com ❤️ para conectar pessoas e serviços de qualidade.*
