// Types
import { TonhubCreatedSession, TonhubSessionAwaited } from "ton-x";
import * as QRCode from "qrcode";

// Methods
import { getSession, waitSession, sendCoins } from './tonhub';


let sessionId = "";
let sessionSeed = "";
let sessionLink = "";
let appPublicKey = "";

function setTonkeeper() {
    // Updates tonkeeper data for login on a web page
    const authRequestUrl = 'hueton.ru/authRequest';
    const loginLink = 'https://app.tonkeeper.com/ton-login/' + authRequestUrl;

    var linkElement = document.getElementById('link-tonkeeper');
    linkElement.setAttribute('href', loginLink)

    new QRCode(document.getElementById('qrcode-tonkeeper'), loginLink);
}

function setTonHub(sessionLink) {
    // Updates tonhub data for login on a web page
    var linkElement = document.getElementById('link-tonkeeper');
    linkElement.setAttribute('href', sessionLink)

    new QRCode(document.getElementById('qrcode-tonhub'), sessionLink);
}

async function init() {
    setTonkeeper();

    const session = await getSession();
    sessionId = session.id;
    sessionSeed = session.seed;

    // По этой ссылке должен перейти пользователь
    setTonHub(session.link);
    
    // Ждём пока перейдёт
    const result = await waitSession(session.id);
    if (result.state === 'revoked' || result.state === 'expired') {
        console.log("TonHub connection failure")
    } else if (result.state === 'ready') {
        console.log(result.wallet.address)
    }
}

init();
