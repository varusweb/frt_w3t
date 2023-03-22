import TonWeb from "tonweb";
import fs from "fs";


const tonweb = new TonWeb(new TonWeb.HttpProvider('https://testnet.toncenter.com/api/v2/jsonRPC', {'apiKey': '3acfd04736431db1dbbe44a3b9921ee8b8ccb31c8373c947f5066a43afb0451b'}));
const Address = TonWeb.Address;
const bytesToHex = TonWeb.utils.bytesToHex;
const base64ToBytes = TonWeb.utils.base64ToBytes;
const Cell = TonWeb.boc.Cell;
const HashMap = TonWeb.boc.HashMap;


export async function get_lottery_data(addr) {
    const result = await tonweb.call(addr, "get_lottery_data", []);
    const hexres = result.stack

    let out = {
        drawTime: parseInt(hexres[0][1], 16),
        price: parseInt(hexres[1][1], 16),
        prizePool: parseInt(hexres[2][1], 16),
        activeTickets: parseInt(hexres[3][1], 16),
        coinPrizes: parseInt(hexres[4][1], 16)
    }

    const prizeNftsCell = Cell.oneFromBoc(base64ToBytes(hexres[5][1]['bytes']));

    let dict = new HashMap(16);
    dict.loadHashMapX2Y(prizeNftsCell,
                        s => TonWeb.boc.CellParser.loadUint(s, 16), // key
                        s => TonWeb.boc.CellParser.loadUint(s, 267)); // value (addr)

    out.prizeNfts = [];

    const size = dict.raw_elements.length;

    for (let i=0; i<size; i++) {
        let bits = dict.raw_elements[i].value.bits;

        // I don't know why, but trash part before addr hash in what
        // returned from dict parsing is 13 bits
        //
        // classic address is 11 bits wc + 256 bits addr hash,
        // maybe there is +2 bits cell marker
        bits.readCursor = 0;
        bits.readBits(13);

        const hashBits = bits.readBits(256);

        const hashRawStr = bytesToHex(hashBits.array);

        const addr = new Address("0:" + hashRawStr);

        out.prizeNfts.push(addr.toString(true, true, true));
    }

    return out;
}

function write(data) {
    // write to json file
    fs.writeFile("lottery_data.json", JSON.stringify(data), function(err) {

        if(err) {
            return console.log(err);
        }

        console.log("The file was saved!");
    });
}

async function run() {
    // cli run of this script
    const addr = process.argv[2];

    if (!addr) {
        console.log("Usage: node get_method.js <addr>");
        return;
    }

    const data = await get_lottery_data(addr);

    write(data);
}

run();
