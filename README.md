# Laravel Bootcamp utilizando Inertia com Vue3

https://bootcamp.laravel.com/inertia/installation

## Requerimentos mínimos

- PHP 8.2 (https://laravel.com/docs/11.x/deployment#server-requirements)
- Composer 2.7
- Node 20

## Instalando o projeto

Clone o respositório
```sh
git clone git@github.com:abnerdsr/laravel-bootcamp-with-inertia-vue.git
```

Instale o projeto
```sh
composer install-chiper
```

Agora abra um outro terminal para manter o worker das filas rodando

Rode os workers das filas
```sh
php artisan queue:work
```

Pronto, ja pode acessar o projeto em
```text
http://127.0.0.1:8000
```

Você pode visualizar os logs e emails gerados em
```text
http://127.0.0.1:8000/log-viewer
```

## Testes

Para rodar os testes execute
```sh
./vendor/bin/pest
```

## Observações

se o servidor cair ou se quiser rodar em algum daemon 

você pode subir ele novamente com o comando abaixo
```sh
php artisan serve
```

se quiser ver alterações de front em tempo real
```sh
npm run dev
```

se fizer alterações nos eventos que são executados por fila, reinicie o queue worker