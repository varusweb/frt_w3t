import express from 'express';
import path from 'path';
import cors from 'cors';
import axios from 'axios';
import { engine } from 'express-handlebars';
import { TonConnectServer, AuthRequestTypes } from '@tonapps/tonconnect-server';

import { sign } from './crypt';


// Перед продакшном хорошо бы запустить npx tonconnect-generate-sk и поставить
// вывод в эту переменную
const staticSecret = 'CAB0h0Hl4q3DudmPOE6ZR9FLwevT3OMNIAfq5uZ3P8o=';

const port = 8444
const host = "127.0.0.1"
const hostname = `fortuna.web3ton.pro`;
  

async function init() {
  const tonconnect = new TonConnectServer({ staticSecret });

  const app = express();

  // Может быть можно как-то по-другому организовать этот сервер, но у меня
  // получилось только так
  app.use(cors());
  app.engine("handlebars", engine());
  app.set("view engine", "handlebars");
  app.set("views", path.resolve(__dirname, "./views"));

  // По этому пути будет формироваться request для тонкипера, для авторизации
  app.get('/authRequest', (req, res) => {
    const token = req.query.token as string;
    console.log("New request with token", token);
    const request = tonconnect.createRequest({
    image_url: 'https://web3ton.pro/images/rarity-item-placeholder.jpg',
      return_url: `${hostname}/tonconnect`,  // адрес кнопки Back to Site
      items: [{
        type: AuthRequestTypes.ADDRESS,
        required: true
      }, {
        type: AuthRequestTypes.OWNERSHIP,
        required: true
      }],
    }, {
      token: token,
    });

    res.send(request);
  });

  // Сюда пойдёт пользователь после логина, принесёт с собой req, где будет
  // храниться информация о логине
  app.get('/tonconnect', async (req, res) => {
    try {
      const encodedResponse = req.query.tonlogin as string;
      // Вот тут расшифровка
      const response = tonconnect.decodeResponse(encodedResponse);

      // То, что мы передадим на страницу успеха
      let message = '';
      let wallet = '';

      const token = response.sessionData.token as string;
      for (let payload of response.payload) {
        // Мы допускаем несколько типов логина: просто любым кошельком и
        // кошельком, но подтвердив, что ты его владелец
        switch (payload.type) {
          case AuthRequestTypes.OWNERSHIP: 
            // Проверяем хитромудрой системой безопасности точно ли владелец
            const isVerified = await tonconnect.verifyTonOwnership(payload, response.client_id);

            message = isVerified 
              ? `${payload.address} (ton-ownership)`
              : `ton-ownership is NOT verified!`

            wallet = payload.address;
            break;

          case AuthRequestTypes.ADDRESS: 
            message = `${payload.address} (ton-address)`
            wallet = payload.address;
            break;
        }
      }

      if (token) {
        if (!token.startsWith("bot:")) {
          const post_data = {
            token: token,
            wallet: wallet
          }
          const result = await axios.post('https://web3ton.pro/tkcallback.php',
                                          post_data);
          console.log("Succesfull login with token", token);
          console.log(result.data);

        } else {
          const toSign = token.slice(4) + ":" + wallet;
          const signed = sign(toSign);
          const post_data = {
            message: signed
          }
	  const result = await axios.post('http://5.23.53.104:1101/',
                                          post_data);
          console.log(result.data)
          if (result.data.status == 'ok') {
            res.redirect("https://t.me/Web3TON_Private_Bot?start=check");
            console.log("Succesfull bot login with token", token);
          } else {
            res.redirect("https://t.me/Web3TON_Private_Bot?start="
                         + result.data.status);
            console.log("Unsuccesfull bot login with token", token);
          }
          return;
        }

      } else {
        res.render("success", {
            layout: false,
            userWallet: message
        });
      }

    } catch (error) {
      console.log(error);
      res.status(400).send({ error });
    }
  });

  // Я нашёл как отрисовывать главную страницу только таким образом
  app.get('/', (req, res) => {
    res.render("index", {
        layout: false,
        requestEndpoint: `${hostname}/authRequest`
    });
  });

  app.listen(port, host, () => {
    console.log(`Server running at https://${hostname}/`);
  });
}

init();
