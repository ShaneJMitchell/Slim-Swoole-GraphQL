# Slim PHP with Swoole - GraphQL

###

##### In order to run locally, you will first need to:
 * Install Swoole: https://www.swoole.co.uk/docs/get-started/installation
 * Create a .env file based on the .env.example and set whatever values you need

Then, you can run the project locally by running the following command from project root:

`php public/index.php`

Then to go 'localhost:8888' in your browser. (Unless you update the port in the instantiation of the Swoole\Http\Server class)

##### Or, you can run the project locally inside of docker container by first building the image (also from project root):
 * Create a .env file based on the .env.example and set whatever values you need

Run `docker build .` OR `docker build . --build-arg PORT=8888` if you want to change the forwarded port to 8888

This should return an image hash when it completes.  You take that returned value (we will refer to it as ${IMAGE_HASH}) and run the following command:

`docker run -d -p 8888:8888 ${IMAGE_HASH} php /src/public/index.php`

You can remove the -d if you don't want the container to run in detached mode.

You can also change the ports that are forwarding into the container if you are already using port 8888.
