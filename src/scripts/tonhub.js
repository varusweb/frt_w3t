import {TonhubConnector, TonhubCreatedSession, TonhubSessionAwaited, TonhubTransactionRequest, TonhubTransactionResponse } from "ton-x";


export const connector = new TonhubConnector({network: "sandbox"});

export async function getSession() {
    const session = await connector.createNewSession({
        name: 'NFT Web3TON',
        url: 'https://web3ton.pro/'
    });
    console.log(session);
    return session;
}

export async function waitSession(sessionId, timeoutMin = 5) {
    const session = await connector.awaitSessionReady(sessionId, timeoutMin * 60 * 1000);
    console.log(session);
    return session;
}

export async function sendCoins(to, amount, message, sessionSeed, appPublicKey, timeoutMin = 5) {
    const request = {
      seed: sessionSeed,
      appPublicKey: appPublicKey,
      to: to,
      value: amount.toString(),
      timeout: timeoutMin * 60 * 1000,
      text: message,
    //   payload: "te6cckEBAQEAUgAAn1/MPRQAAAAAAAADCYAKPvFPCYEV50Xs1IxFEXymxw/6eFx+moz4Nq7/qBZlv/AART9XH37phxxDoNGmcKc7t9AS4iR76HPj2Oj0dgSY82wI8rcSpA=="
    };
    
    const response = await connector.requestTransaction(request);

    console.log(response);

    if (response.type === 'rejected') {
        // Handle rejection
    } else if (response.type === 'expired') {
        // Handle expiration
    } else if (response.type === 'invalid_session') {
        // Handle expired or invalid session
    } else if (response.type === 'success') {
        // Handle successful transaction
        const externalMessage = response.response; // Signed body of external message that was sent to the network
        console.log(externalMessage);
    } else {
        throw new Error('Impossible');
    }
  }

