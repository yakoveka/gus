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
```
1. Configure self-hosted runner:
https://github.com/yakoveka/gus/settings/actions/runners/new?arch=x64&os=linux
2. Install docker on instance
3. Start `docker compose` from the **Build and run** section
