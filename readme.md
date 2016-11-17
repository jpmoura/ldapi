**You can see the English version of this README by clicking [here](#ldapi---enus).**

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

# 1. Instalação

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

## 1.1 Requisitos

Os requisitos para o funcionamento da API são os mesmos requisitos do *framework* [Lumen](), acrescidos dos
plugins PHP para SQLite 3:

* Versão do PHP >= 5.6.4;
* Extensão PHP OpenSSL;
* Extensão PHP PDO para SQLite;
* Extensão PHP SQLite;
* Extensão PHP Mbstring.

# 2. Configuração

A configuração pode ser feita manipulando diretamente a
[base de dados](./database/database.sqlite) em [SQLite3](https://sqlite.org/about.html)
ou através do painel de controle presente na API, que pode ser acessado através da URL da API
acrescida de `/admin`. A base de dados é constituída das seguintes tabelas:

* Fields;
* Settings;
* Users.

## 2.1 Tabela Fields
 
 Essa tabela se refere aos campos presentes no servidor AD que poderão ser usados como
 parâmetros, tanto de filtro de pesquisa quanto na recuperação de dados. Ela age como uma espécie de dicionário,
 convertendo o nome original do campo no servidor em um nome que possa ser identificado pelos usuários da API e
 vice-versa. Ela é constituída
 de duas colunas:
 
| Coluna | Tipo de Dado   | Descrição                       |
|:------:|:--------------:|:-------------------------------:|
| name   | varchar(50) PK | Nome do atributo no servidor AD |
| alias  | varchar(50)    | Apelido do atributo             |

## 2.2 Tabela Settings

A tabela *settings* se refere as configurações do servidor AD que será utilizado nas pequisas
e autenticação de usuários. Suas colunas são:

| Coluna        | Tipo de Dado           |Descrição                                                                         |
|:-------------:|:----------------------:|:--------------------------------------------------------------------------------:|
| server        | varchar(15) PK         | Endereço IP do servidor AD                                                       |
| user          | varchar(20)            | Usuário com permissão de leitura no servidor AD                                  |
| domain        | varchar(20)            | Domínio base do usuário de leitura                                               |
| pwd           | varchar(255)           | Senha do usuário de leitura                                                      |
| user_id       | varchar(25)            | Atributo que identifica unicamente as entidades e que será usado na autenticação |
| struct_domain | varchar(100)           | Domínio base dos usuários que serão autenticados pela API                        |
 
## 2.3 Tabela User

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

# 3. Como usar

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

## 3.1 Autenticação

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
na base de dados. Consirando que os valores dos atributos no servidor AD sejam, respectivamente, `Usuário`
e `Teste`, a resposta da API considerando que o `username` e `password` então certos, seria:

```json
{
    "primeironome": "Usuário",
    "ultimonome": "Teste"
}
```

Se o usuário não for autenticado, a resposta da API tem o status 401, que significa que o usuário
não está autorizado e o corpo da mensage, que nos casos de erro não estão codificados em JSON e sim
em texto puro, contém a mensagem dizendo qual é o erro, que pode ser desde credenciais inválidas
até configurações inválidas da própria API.

## 3.2 Pesquisa

Para a pesquisa de dados, foram abordadas duas situações. Uma é quando o usuário da API tem conhecimento da sintaxe
de pesquisa em servidores AD e deseja escrever o seu próprio filtro usando expressões puras (*raw expressions*).

A
segunda abordagem é aquela que foi uma das motivações para a criação desta API, que é o a pesquisa através de uma
sintaxe que tem a intenção de ser mais simples para usuários que não estejam familiarizados com a sintaxe de servidores
AD.

Nas duas seções a seguir, são apresentadas as particularidades de cada uma.

### 3.2.1 Pesquisa usando sintaxe de AD

Para ter acesso a esse tipo de pesquisa, a URL reconhecida pela API é a URL base da API acrescida de `\searchLikeLdap`.
Naturalmente, é necessário que o usuário conheça o nome dos campos que existem no servidor, uma vez que o filtro
será usado repassado para o servidor na forma que ele for informado, ou seja, não existe tradução de apelidos para
o nome real do atributo no filtro.

Nesse tipo de pesquisa, são necessários dois campos no corpo da requisição:

* `filter`: corresponde ao filtro que será usado na pesquisa. Deverá estar no formato nativo;
* `attributes`: corresponde aos campos que deverão ser retornados na pesquisa. Deverão estar em um vetor (*array*)
unidimensional usando os apelidos dos campos definidos na configuração da API.

A falta de qualquer um desses campos no corpo JSON da requisição resultará em uma resposta de erro por parte da API
com status 400. É importante lembrar que a requisição também deve conter os cabeçalhos HTTP de tipo de conteúdo
(*Content-Type*) e de autorização (*Authorization*).

#### 3.2.1.1 Exemplo de uso

Assumindo que exista os campos `cn` e `sn` no servidor AD e que se queria buscar por entidades que tenham o `cn` igual
a `Usuário` e `sn` igual a `Teste` e ainda se queira os campos `grupo` e `email` no resultado, o corpo da pesquisa
seria:

```json
{
    "filter": "(&(cn=Usuário)(sn=Teste))",
    "attributes": ["grupo", "email"]
}
```

Cada objeto encontrado pelo resultado é retornado como um objeto dentro da resposta JSON. Caso não seja encontrada
nenhuma entidade, a resposta será retornada como um documento JSON vazio. Um possível resposta para a pesquisa a cima
seria:

```json
[
    {
        "grupo": "teste",
        "email": "teste1@teste.com"
    },
    {
        "grupo": "teste",
        "email": "teste2@teste.com"
    }
]
```
Neste caso, foram encontradas duas entidades que correspondem ao filtro informado, cada uma encapsulada em um objeto.

### 3.2.2 Pesquisa usando sintaxe da API

O objetivo da sintaxe desenvolvida para ser usada com essa API é de simplificar filtros de pequisas que sejam
muito complexos além de proteger o nome real dos campos presentes no servidor LDAP, já que o administrador pode
cadastrar somente os atributos, com seus respectivos apelidos, que poderão ser usados nas pesquisas e resultados.
Ou seja, mesmo que o usuário conheça o nome real de um campo de informação crítica como senha ou algum dado
de identificação de uma pessoa como CPF, ele não será capaz de recuperá-lo desde que o atributo não esteja cadastrado.

Outra vantagem da utilização desse funcionalidade é possibilitar que o administrador possa modificar o nome dos campos
dos atributos no servidor AD sem que os usuários API necessitem também alterar seus códigos de pesquisa, desde que os
apelidos dos campos se mantenham.

Essa pesquisa pode ser acessada adicionado `/search` a URL base da API.
Para esse tipo de pequisa, o corpo JSON da requisião HTTP deve conter os seguintes
campos:

* `baseConnector` representando o conector binário entre os diferentes filtros que serão informados. Os possíveis
 valores são `and` representando o E (&), `or` representando OU (|) e `not` representando a negação (!);

* `filters`: representa um os filtros de pesquisa do usuário. Consiste em um vetor unidimensional (*array* ) contendo
objetos JSON que representam cada guarda do filtro;

* `attributes`: representa os atributos que seão retornados pela pesquisa. Consistem em um vetor unidimensional
(*array*) contendo os apelidos dos atributos desejados no resultado da pesquisa;

* `searchBase` (Opcional): representa o a base em que a pesquisa irá acontecer mas caso não seja informada, será usada
a base do usuário leitor. Útil para usuários que conhecem a estrutura de entidades do servidor AD;

A falta de um destes atributos, com execeção de `searchBase`, que é opcional, resultar um resposta de status igual a
400, juntamente com um corpo em texto puro informando qual foi o erro da requisição.

#### 3.2.2.1 Filtros de pesquisa

Cada elemento do vetor de filtro é um objeto JSON contendo o apelido do atributo e seu valor é um vetor unidimensional
(*array*) contendo o tipo de combinação e o valor do campo para a combinação e deve ser informado **só e somente só**
nesta ordem. Caso a ondem seja invertida, resultará em erro. Os operadores de combinação que podem ser:

| Tipo                  | Valor             |
|:---------------------:|:-----------------:|
| Igualdade (=)         | `equals`          |
| Presente em (*)       | `present`         |
| Aproximação  (~=)     | `approximately`   |
| Menor ou igual a (<=) | `lesserOrEquals`  |
| Maior ou igual a (>=) | `greaterOrEquals` |
| Menor que (<)         | `lesserThan`      |
| Maior que (>)         | `graterThan`      |

Além disso, um objeto de filtro pode ter ou não campo `operator` que corresponde ao conector binário especificamente
destes campos. É útil para pesquisas possam ter um outro valor. Caso esse campo seja omitido, o conector binário
será igual ao conector base (`baseConnector`). Os valores possíveis para o campo `operator` são os mesmo possíveis
para o campo `baseConnector`. Um exemplo de filtros seria:

```json
    "baseConnector": "or",
    "filters":
    [
      {
        "grupo": ["equals", "teste"],
        "primeironome": ["equals", "Usuário"],
        "operator": "and" 
       },
      {
        "ultimonome": ["equals", "Teste"]
      }
    ]
```

Esse filtro representa uma busca por todas as entidades cujo o grupo o conteúdo do atributo seja igual a "teste" e que
o primeiro nome seja igual a "Usuário" OU (representado pelo `baseConnector`) que o atributo "ultimonome" seja igual a
"Teste". Em uma linguagem como PHP essa pesquisa corresponderia algo como:
```php
if ( ($grupo === "teste" && $primeironome === "Usuário") || $ultimonome === "Teste")
```

Também é possível usar *wildcards* no valor do campo como *, como em SQL ou expressões regulares. 

#### 3.2.2.2 Resultado da pesquisa

Pesquisas que estejam bem formatadas possuem 3 campos no documento JSON de retorno:

* count: representa a quantidade de registros retornados pela pesquisa;
* ldapSearch: representa como o filtro foi construído em sintaxe de AD para realização da pesquisa;
* result: contém todos os registros retornados pela pesquisa, que é um vetor unidimensional contendo cada registro
encapsulado como um objeto JSON.

#### 3.2.2.3 Exemplo de uso

Assumindo que o nas configurações da API temos os apelidos de campos `grupo`, `primeironome`, `nomecompleto` e `email`
cadastrados e caso seja necessário buscar por todas as entidades na base `ou=People,dc=server,dc=com` cujo ou o `grupo`
seja iniciado pela letra A ou o `primeironome` seja igual a "JOSE" e seja requerido os atributos `nomecompleto`, `grupo`
e `email` como atributos de retorno, teríamos a requisição HTTP com o seguinte corpo JSON:

```json
{
	"baseConnector": "or",
	"filters":
	[
		{"grupo": ["equals", "A*"], "primeironome": ["equals", "JOSE"]}
	],
	"returningAttributes": ["nomecompleto", "grupo", "email"],
	"searchBase": "ou=People,dc=server,dc=com"
}
```

Como o campo `operator` foi omitido no único objeto de filtro no campo `filters`, é assumido que o conector binário
entre esses parâmtros é igual ao `baseConnector`, que nesse caso é igual a `or` (OU).

Um possível resultado para essa pesquisa poderia ser:

```json
{
  "count": 2,
  "ldapSearch": "(|(ou=A*)(cn=JOSE))",
  "result": [
    {
      "nomecompleto": "JOSE CICLANO",
      "grupo": "FINANCEIRO"
    },
    {
      "nomecompleto": "FULANO DA SILVA",
      "grupo": "ADMINISTRATIVO"
    }
  ]
 }
```

# 4. Função de ajuda

Para essa primeira versão, existe uma função de ajuda que retorna todos os apelidos dos
campos disponíveis. Para acessá-la, basta enviar uma requisição POST vazia para o endereço
da URL base da aPI acrescida de `/aliases` e um vetor unidimensional (*array*) será retornado
com todos os apelidos de atributos cadastrados.

# 5. A Fazer

* Migrações para automatizar ainda mais a implementação da API;
* Verificar o *middleware* de autenticação para checar a possibilidade de acessar o painel de controle
da API através de navegadores que suporte URL's do tipo http://Usuario:Senha@url.da.api/admin;
* Otimizar códigos de pesquisa;
* Tratar exceções de CRUD do painel de controle;
* Possibilitar que o servidor seja informado não só por IP;
* Adicionar opção de mudar a base da pesquisa para pesquisas feitas em sintaxe de AD;
* Utilizar bibliotecas CSS e Javascript localmente ao invés de remoto.

# LD(AP)I - enUS

LD(AP)I is a REST API for easy access data in AD servers. The main goals of this API are:

* To easier the authentication process for applications that have they authentication base
on AD;
* To facilitate the access of data in those AD server via a more friendly search syntax.

It was build using the version 5.3 of [Lumen](https://lumen.laravel.com/) framework,
which is a PHP framework for micro-services, and using the LDAP protocol to access the AD server
Every single communication between client and API is done via HTTP requests and POST method
that contains a JSON body. The reason for this is to evade too long URLs that could throw
erros when using complex search filters.

JSON was chosen due its acceptance and ease of use by almost any application.

# 1. Installation

After clone this repository and move it to the right directory in your HTTP server,
in the API root directory, run the following command:

```bash
$ composer install
```

Depending on your configurations, maybe will be necessary run with Super User permission
(`sudo` or `su` in Linux distributions). Also, it is probably requested thar you give
special permissions for all files and folders and the root directory itself. You can
do this by running:

```bash
$ sudo chown Your_User:www-data Path_To_API_Root_Directory -R
$ sudo chmod 775 -R Path_To_API_Root_Directory
```

Replace the `Your_User` and `Path_To_API_Root_Directory` placeholders with you user and
the API root directory in the server machine respectively. These configurations were test
in a Linux Environment.

## 1.1 Requirements

This API has the same requirements of Lumen to work as expect plus some SQLite PHP extensions.
The requirements are:

* PHP Version >= 5.6.4;
* PHP OpenSSL extension;
* PHP PDO for SQLite extension;
* PHP SQLite extension;
* PHP Mbstring extension;

# 2. Configuration

LD(AP)I can be customized by manipulating directly the SQLite database or via its own
dashboard, which can be accessed by adding `/admin` after API base URL. The API database
has three tables:

* Fields;
* Settings;
* Users.

## 2.1 Fields table
This table refers to all fields that can be retrieved in search results by the API users.
This table is like a dictionary, that translates an user-friendly AD attribute to the
real attribute in AD server and vice-versa. Its structure is:

| Column | Data type      | Description                 |
|:------:|:--------------:|:---------------------------:|
| name   | varchar(50) PK | Attribute name in AD server |
| alias  | varchar(50)    | Alias of the attribute      |

## 2.2 Settings table
This table contains the required information that keep the API running. It has the
following structure:

| Column  | Data type      | Description                                                  |
|:-------:|:--------------:|:------------------------------------------------------------:|
| server  | varchar(50) PK | Server IP address                                            |
| user    | varchar(20)    | User that will read the data in AD server                    |
| domain  | varchar(20)    | Base domain of reader user                                   |
| pwd     | varchar(255)   | Password of reader user                                      |
| user_id | varchar(25)    | Attribute that refers the unique identification of a entity  |

## 2.3 Users table
This table has all the users whom can use the API. If you want to make your API public
by default, just remove the `auth` middleware in [routes](.\routes\web.php) file.
However, if do want to protect your data, you need to know the user table structure,
which is:

| Column      | Data type      | Description                                     |
|:-----------:|:--------------:|:-----------------------------------------------:|
| username    | varchar(50) PK | User name                                       |
| password    | varchar(64)    | User password                                   |
| description | text           | User description                                |
| role        | string(5)      | User role. It could be either `admin` or `user` |

By default, the table has two records, one for administration with the highest privileges
and one for user, who can only uses the API features.

# 3. Usage

After set all configurations, users will have access to the two features of this API:
user authentication and data search. Both features can be access via HTTP requests
with POST method and they **should have** the Authorization and Content-Type headers.

The authorization header must have the authentication type, which is the **Basic** 
type for this API, separated by a whitespace from the user name and password,
whose are separated by colon (:) and encoded in base64. For instance, an user who
has a user name equals `system` and password equals `secret` should have an authorization
header like:

```
Authorization: Basic c3lzdGVtOnNlY3JldA==
```

The word `c3lzdGVtOnNlY3JldA==` represents the word system:secret encoded in base64.

Also, the Content-type header must specifies that the request's content is a JSON by 
being equals to:

```
Content-Type: application/json
```

It is important that the request's body is well formed, otherwise it will cause a error
response.

## 3.1 Authentication
To authenticates an user using this API, it is necessary to send a POST HTTP request
to the base URL of API appended with `/auth`. The request's body must be a JSON document
that contains, at least, two parameters:

* username: the user name from the user you want to authenticate;
* password: the user password;

For example, if you want to authenticate the same uses from previous example, the 
request's body it would be:

```json
{
    "username": "system",
    "password": "secret"
}
```

If valid, the response for this request would be:

```json
{
    "authenticated": true
}
```

If the authentication fails, a response with a status different from 200 will be returned.
It's valid to reinforce that every response which has a status code not equals to 200, it
is a error response.

For applications that need data from the authenticated user, just insert a field called
`attributes` which must contains a array that has all the desired attributes of the user.
The same request to authenticate the same `system` user that retrieves his first name and
last name could it be something like:

```json
{
    "username": "system",
    "password": "secret",
    "attributes": ["firstname", "lastname"]    
}
```

Remember that `firstname` and `lastname` are aliases of real attributes in AD server and
they must exist in the fields table. A possible response of this request would be:

```json
{
    "firsname": "A simple",
    "lastname": "Test"
}
```

## 3.2 Search

For data search, two approach were made. The first is when the API uses knows the
AD search syntax and wants do search using that syntax for a particular reason. So,
there's a option for search data using raw AD search expressions.

The second approach was the goal of this API, to search using a simple syntax than AD native
syntax. This could be useful for users that don't know the AD native syntax or think that
it to complicated.

In the follow sections, each approach will be presented.

### 3.2.1 Using native AD syntax
Users can access this feature via sending request to the base API URL added with
`/searchLikeLdap`. Usually, it's requested that the users who use this feature know the
AD structure and its attributes once this features don't have the "translation" of aliases
into real AD attributes.

This kind of research requires two fields in request's body:

* `filter`: native form of a search filter in AD;
* `attributes`: refers to the aliases of attributes that will be present in the
search result. Must be an array.

If one of these two fields is not present in the request's body, the API will return a 
error response that has a status code equals 400 which means a bad request. Also,
don't forget do include the Content-Type and Authorization headers in every request.

#### 3.2.1.1 Example

Assuming that the AD server has two fields `cn` and `sn` and it's requested to search for
the group and e-mail of all users who have the first name equals `A simple` ans surname
equals `Test`, the request's body would be:

```json
{
    "filter": "(&(cn=A Simple)(sn=Test))",
    "attributes": ["group", "mail"]
}
```

For this search we expect that every entity that matches these two parameters have in
the result their group and e-mail address. A possible response for this request would be:

```json
[
    {
        "group": "test",
        "mail": "test1@ldapi.com"
    },
    {
        "group": "test",
        "mail": "test2@ldapi.com"
    }
]
```

Each entity found is encapsulated is one JSON object but, if no entity is found,
a empty JSON is returned by API.

### 3.2.2 Using API syntax
The goal of the syntax developed for this API was to simplify the search for non-advanced
users, to make complex search filters easy to write and read and also to protected the
AD server from attacks when the attacker knows the critical fields present in the server.
In other word, even if the attacker knows the fields, he can't retrieve then if they are not
in the fields table.

Another positive point of using this API is to permit the administrator to modifies the
attribute name in AD server without make all applications that use the server to change
their code since he keeps the same alias for the field.

This search cab be access by send POST requests to the API base URL added with `/search` and 
its required the following fields in JSON request's body:

* `baseConnector`: represents de binary connector between filter components;
* `filters`: an array of JSON objects where each object is a different filter;
* `attributes`: array that contains the aliases of all desired attributes in the response;
* `searchBase` (Optional): useful for users that knows the AD server structure;

If you miss just one of these attributes, excepted by `searchBase`, will cause the API
to return a error response.

#### 3.2.2.1 Search filters

Each element in the `filters` array is a JSON object that represents a guard of the filter.
They should have the alias of an attribute and the value of this entry must be an array
where the first position represents the logical match operator for the attribute and the 
second value of the array is the value that fields must match according the operator.
If you change the order, will cause an error. The possible match operators are:

| Type                 | Value             |
|:--------------------:|:-----------------:|
| Equality             | `equals`          |
| Presence             | `present`         |
| Approximation        | `approximatelly`  |
| Lesser or equals to  | `lesserOrEquals`  |
| Greater or equals to | `greaterOrEquals` |
| Lesser than          | `lesserThan`      |
| Grater than          | `greaterThan`     |

Also, exists a optional field in the JSON object named `operator` which represents the
binary connector between the fields of a same guard. This could be useful for complex
searches that have a great number of guards and each guard has a different binary connector
from `baseConnector`. The `operator` field can assume the same values shown in the table
above. To clarify, a example of filter:

```json
    "baseConnector": "or",
    "filters":
    [
      {
          "group": ["equals", "test"],
          "firsname": ["equals", "A simple"],
          "operator": "and"
      }
      {
          "lastname": ["equals", "Test"]
      }
    ]
```

This filter is looking for some user that has his group attribute equals to `test` and his
first name equals `A simple` OR (`baseConnector`) his last name equals to "Test".
This piece of filter could be interpreted like this if guard in PHP:

```php
if ( ($group === "test" && $firstname === "A simple") || $lastname === "Test")
```

It is also possible to use wildcards in this searches, just like in SQL for instance.

#### 3.2.2.2 Search results

Search requests that are well formed will cause the response to have three fields in its
body:

* `count`: total of entities found;
* `ldapSearch`: represents how the filter was translated to AD syntax, just for infomational
purposes;
* `result`: an array of JSON objects where each object is a entity returned by the search.

#### 3.2.2.3 Example

Assuming that we have in our database the attributes with the related aliases of `group`,
`firstname`, `fullname` and `mail` and we need to search for entities in the base
`ou=People,dc=ldapi,dc=com` which the `group` name starts with character 'A' or the `firstname`
is equals `Fred` and we want the `fullname` and `mail` of the entities who satisfies these
requirements. A possible request's body would be:

```json
{
    "baseConnector": "or",
    "filters":
    [
        {"group": ["equals", "A*"], "firstname": ["equals", "Fred"]}
    ],
    "returningAttributes": ["fullname", "mail"],
    "searchBase": "ou=People,dc=ldapi,dc=com"
}
```

Notice that the `operator` field was omitted, so the API assume that the binary connector
between `group` and `firstname` is equals to `baseConnector`, which is `or` in this particular
case. Therefore, a possible response for this request is:

```json
{
  "count": 2,
  "ldapSearch": "(|(ou=A*)(cn=Fred))",
  "result": [
    {
      "fullname": "Fred Flinstone",
      "group": "Human Resources"
    },
    {
      "fullname": "John Doe",
      "group": "Accounting"
    }
  ]
 }
```

# 4. Helper function

For this first version, there is a helper function which can be access via POST request to
the API base URL added by `/aliases` and it will return an array that contains all
available aliases.

# 5. TODO

* Native Lumen migrations;
* Check if Auth middleware can handle request from browser like http://user:password@ldap.base.url
* To optimize search methods;
* Catch all CRUD exceptions in dashboard;
* Allow administrators do inform the AD server address not only by IP;
* Add option to chance the search base in `/searchLikeLdap` feature;
* Use local CSS and Javascript libraries in dashboard.
