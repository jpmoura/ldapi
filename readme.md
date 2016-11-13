# LD(AP)I - ptBR

LD(AP)I é uma API REST que visa facilitar o uso dos dados contidos em um servidor AD
(Active Directory).
Os objetivos principais dessa API são:

* Facilitar o processo de autenticação de aplicações que usam autenticação baseadas em
servidores AD;
* Facilitar a recuperação de dados de tais servidores através de pesquisas de sintaxe
mais amigável.

Essa API foi desenvolvida usando o *framework* PHP [Lumen](https://lumen.laravel.com/) na
versão 5.3, usando o protocol LDAP para acesso ao servidor. Toda comunicação entre a API
e o cliente é feita através de requisições HTTP do tipo POST afim de evitar que a URL
da requisição seja muito grande, o que poderia causar erros em caso de filtros de
pesquisa muito grandes.

Toda requisição e resposta entre API e cliente é feito usando
o formato de dados JSON e a sua escolha se deve a facilidade de leitura, tanto a nível
de máquina quanto humano, e também pelo fato de poder ser convertido facilmente para
qualquer outro tipo dado e assim podendo ser exportado, virtualemente, para qualquer
aplicação.

# Instalação

Após clonar este repositório e movê-lo para o devido diretório dentro do servidor WEB
que irá hospedar essa API, navegue pelo terminal até o diretório raiz da API e
dê o seguinte comando:

```bash
composer install
```

Dependendo das suas configurações, será necessário efetuar o comando com permissões de
super usuário (`sudo`). Talvez seja necessário também dar permissões especiais a todos
os arquivos do diretório. Isso pode ser feito através do comando:

```bash
sudo chown SeuUsuario:www-data CaminhoParaDiretórioDaAPI -R
```

Substitua as palavras `SeuUsuario` e `CaminhoParaDiretórioDaAPI` de acordo com seu
usuário no servidor WEB e o caminho (path) do diretório onde os arquivos da API
se encontram.

# Como usar

# LD(AP)I - enUS

LD(AP)I is a REST API for easy access data in AD servers. The main goals of this API are:

* To easier the authentication process for applications that have they authentication base
on AD;
* To facilitate the access of data in those AD server via a more friendly search syntax.

It was build using the version 5.3 of [Lumen](https://lumen.laravel.com/) framework,
which is a PHP framework for micro-services.

# Installation

# Usage