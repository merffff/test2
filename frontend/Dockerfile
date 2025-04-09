FROM node:16

WORKDIR /app

COPY package*.json ./

RUN npm install --save react-router-dom axios
RUN npm install

COPY . .

EXPOSE 3000

CMD ["npm", "start"]