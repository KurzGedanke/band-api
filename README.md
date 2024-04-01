# Band api

Band API provides a json api with a symfony easyadmin.
Simply add your bands, festival and stages and retrive these information via the rest api.

## Routes

`/api/festivals`

`/api/festivals/{festival}/bands`

`/api/festivals/{festival}/{bandSlug}`

`/api/festivals/{festival}/stages`

`/api/festivals/{festival}/stages/{stageName}`

`/api/festivals/{festival}/stages/{stageName}/timeslots`

## Development Server

```sh
$ composer install
```

```sh
$ docker-compse up
```

```sh
$ php bin/console doctrine:migrations:migrate
```

```sh
$ symfony server:start
```

## Deployment
