# First, run ```aws ecr get-login --no-include-email --region us-west-2```
# Then copy the result into a terminal and execute it

# Build php-fpm docker container
docker build --file docker/php/Dockerfile -t mymoneymanager_php:latest .

# Build nginx docker container
docker build --file docker/nginx/Dockerfile -t mymoneymanager_nginx:latest .

# Tag & push containers to ECS
docker tag mymoneymanager_php:latest 964400098929.dkr.ecr.us-west-2.amazonaws.com/mymoneymanager_php:latest
docker tag mymoneymanager_nginx:latest 964400098929.dkr.ecr.us-west-2.amazonaws.com/mymoneymanager_nginx:latest
docker push 964400098929.dkr.ecr.us-west-2.amazonaws.com/mymoneymanager_php:latest
docker push 964400098929.dkr.ecr.us-west-2.amazonaws.com/mymoneymanager_php:latest