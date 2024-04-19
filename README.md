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
ssh root@165.227.138.54
```
1. Install docker on instance
2. Configure user for self-hosted runner
```
sudo useradd -m gh_actions
```
3. Add permissions for gh_actions user to use docker.sock
```
sudo usermod -aG docker $USER
```
4. Configure self-hosted runner:
https://github.com/yakoveka/gus/settings/actions/runners/new?arch=x64&os=linux
5. Start `docker compose` from the **Build and run** section
