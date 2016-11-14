# LD(AP)I - ptBR

LD(AP)I é uma API REST que visa facilitar o uso dos dados contidos em um servidor AD
(Active Directory).
Os objetivos principais dessa API são:

* Facilitar o processo de autenticação de aplicações que usam autenticação baseadas em
servidores AD;
* Facilitar a recuperação de dados de tais servidores através de pesquisas com sintaxe
mais amigável.

Essa API foi desenvolvida usando o *framework* PHP [Lumen](https://lumen.laravel.com/) na
versão 5.3, usando o protocol LDAP para acesso ao servidor. Toda comunicação entre a API
e o cliente é feita através de requisições HTTP do tipo POST afim de evitar que a URL
da requisição seja muito grande, o que poderia causar erros em caso de filtros de
pesquisa muito grandes.

Toda requisição e resposta entre API e cliente é feito usando
o formato de dados JSON e a sua escolha se deve a facilidade de leitura, tanto a nível
de máquina quanto humano, e também pelo fato de poder ser convertido facilmente para
qualquer outro tipo dado, podendo ser exportado, virtualmente, para qualquer
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
sudo chown Seu_Usuario:www-data Caminho_Para_Diretório_Da_API -R
sudo chmod 775 -R Caminho_Para_Diretório_Da_API
```

Substitua as palavras `Seu_Usuario` e `Caminho_Para_Diretório_Da_API` de acordo com seu
usuário no servidor WEB e o caminho (*path*) do diretório onde os arquivos da API
se encontram. Essas configurações são baseadas em sistemas Linux utilizando o
[servidor Apache](https://httpd.apache.org/).

# Configuração

A configuração pode ser feita manipulando diretamente a
[base de dados](./database/database.sqlite) em [SQLite3](https://sqlite.org/about.html)
ou através do painel de controle presente na API, que pode ser acessado através da URL da API
acrescida de `/admin`. A base de dados é constituída das seguintes tabelas:

* Fields;
* Settings;
* Users.

## Tabela Fields
 
 Essa tabela se refere aos campos presentes no servidor AD que poderão ser usados como
 parâmetros, tanto de filtro de pesquisa quanto na recuperação de dados. Ela é constituída
 de duas colunas:
 
 | Coluna        | Tipo de Dado           | Descrição                                              |
 |:-------------:|:----------------------:|:------------------------------------------------------:|
 | name          | varchar(50) PK         | Corresponde ao nome do atributo no servidor AD         |
 | alias         | varchar(50)            | Apelido que irá ser usado nas pesquisas e autenticação |

## Tabela Settings

A tabela *settings* se refere as configurações do servidor AD que será utilizado nas pequisas
e autenticação de usuários. Suas colunas são:

 | Coluna        | Tipo de Dado           | Descrição  |
 |:-------------:|:----------------------:|:----------:|
 | server        | varchar(15) PK         | Corresponde ao endereço IP do servidor AD                                                       |
 | user          | varchar(20)            | Usuário com permissão de leitura no servidor AD                                                 |
 | domain        | varchar(20)            | Domínio base do usuário de leitura                                                              |
 | pwd           | varchar(255)           | Senha do usuário de leitura                                                                     |
 | user_id       | varchar(25)            | Atributo no servidor AD que identifica unicamente cada usuário e que será usado na autenticação |
 | struct_domain | varchar(100)           | Domínio base dos usuários que serão autenticados pela API                                       |
 
## Tabela User

A tabela referencia os usuários autorizados a utilizarem a API. Para usuário onde a função
(coluna *role*) seja igual a `admin`, o acesso é permitido ao painel de controle.

| Coluna      | Tipo de Dado   | Descrição                                              |
|:-----------:|:--------------:|:------------------------------------------------------:|
| username    | varchar(50) PK | Nome de usuário                                        |
| password    | string(64)     | Senha do usuário                                       |
| description | text           | Descrição do usuário                                   |
| role        | string(5)      | Papel do usuário. Valores possíveis: `admin` ou `user` |

Por padrão ele vem com dois registros padrões, sendo eles um de administrador (coluna *role* igual
a `admin`) que possui acesso ao painel de contrele sendo o nome de usuário e a senha iguais a
`admin` para que seja possível configurar a API pelo painel de controle. O outro registro
é um usuário de nome e senha igua a `test` para que seja possível realizar testes de permissão
de acesso após feita a configuração.

# Como usar

Após realizada a configuração, o usuário da API tem acesso às duas funções da API: pesquisa e autenticação.
Ambas são feitas realizando requisições HTTP do tipo POST contendo os cabeçalhos de
autorização (*Authorization*) e tipo de conteúdo (*Content-type*).

O cabeçalho de autorização deve conter o tipo de autenticação, que no caso da API é a autenticação
*Basic*, separada por um espaço em branco do nome do usuário e sua senha que são unidos pelo
caracter de dois pontos (:) e codificados em base 64. Por exemplo, um usuário que tenha seu
nome igual a `system` e senha igual a `secret` deverá contar com um cabeçalho de autorização
formatado na seguinte forma:

```
Authorization: Basic c3lzdGVtOnNlY3JldA==
```

A palavra `c3lzdGVtOnNlY3JldA==` corresponde a versão codificada em base 64 da palavra `system:secret`.

O cabeçalho de conteúdo (*Content-type*) deve especificar que o conteúdo é do tipo JSON, ou seja,
deverá ter o seguinte formato:

```
Content-Type: application/json
```

É importante que o corpo da requisição esteja devidamente formato, caso contrário, ocasionará em
uma resposta que contém uma mensagem de erro.

## Autenticação

Para autenticar um usuário é necessário enviar uma requisição POST para a URL da API com `/auth` 
concatenado. O corpo da mensagem deve ser um JSON contendo os campos `user` com o valor do ID
do usuário e o campo `password` contendo a sua senha. Por exemplo, o corpo de uma requisição
que visa autenticar o usuário `system` de senha `secret` é igual a:

```json
{
    "username": "system",
    "password": "secret"
}
```

Uma requisição desse tipo em caso de sucesso retornaria o seguinte JSON:

```json
{
    "authenticated": true
}
```

Em caso de falha, uma página com status diferente de 200 é retornada com o corpo
contendo a mensagem de erro. É importante resaltar que qualquer resposta que tenha o status
retornado pela API diferente de 200 é uma resposta contendo uma mensagem de erro.

Para aplicações que necessitem de informações sobre o usuário, caso ele seje autenticado,
basta inserir o campo `attributes` no corpo JSON da requisição, que deve conter um vetor (*array*)
contendo todos os apelidos dos atributos (*alias*) que o requisitante necessita. Um exemplo desse
tipo de requisição onde é necessário o retorno do atributo `cn`, que em servidores AD normalmente
representa o primeiro nome da entidade, e que cujo o apelido é `primeironome`, bem como do
atributo `sn`, que normalmente representa o último nome, e que cujo o apelido é `ultimonome`
teria um corpo igual a:

```json
{
    "username": "system",
    "password": "secret",
    "attributes": ["primeironome", "ultimonome"]
}
```

O campo attributes não possui limites, sendo possível passar quantos atributos estejam cadastrados
na base de dados.

## Pesquisa

### Pequisa usando campos customizados

### Pesquisa usando sintaxe de AD

#TODO

* Migrações para automatizar ainda mais a implementação da API;
* Verificar o *middleware* de ** para checar a possibilidade de acessar o painel de controle
da API através de navegadores que suporte URL's do tipo http://Usuario:Senha@URI.Da.API;
* Otimizar códigos de pesquisa;
* Tratar exceções de CRUD do painel de controle;
* Possibilitar que o servidor seja informado não só por IP;
* Utilizar bibliotecas CSS e Javascript localmente ao invés de remoto.

# LD(AP)I - enUS

LD(AP)I is a REST API for easy access data in AD servers. The main goals of this API are:

* To easier the authentication process for applications that have they authentication base
on AD;
* To facilitate the access of data in those AD server via a more friendly search syntax.

It was build using the version 5.3 of [Lumen](https://lumen.laravel.com/) framework,
which is a PHP framework for micro-services.

# Installation

# Usage