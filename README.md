### Build and run
```
docker compose up --build --wait -d
```

### Stop
```
docker compose down --remove-orphans
```

### Postgresql
```
psql gus
```

### Working Pages
```
/expenses
/categories
```

### Tailwind
Execute following in php container locally:
```
npm run watch 
```

### DigitalOcean
```
ssh root@64.227.114.41
https://github.com/yakoveka/gus/settings/actions/runners/new?arch=x64&os=linux
```
