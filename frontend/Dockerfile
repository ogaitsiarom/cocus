FROM node:18

WORKDIR /app

COPY package.json ./

RUN rm -rf node_modules package-lock.json \
    && npm install

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev"]