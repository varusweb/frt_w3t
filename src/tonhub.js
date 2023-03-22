"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
exports.__esModule = true;
exports.sendCoins = exports.waitSession = exports.getSession = exports.connector = void 0;
var ton_x_1 = require("ton-x");
exports.connector = new ton_x_1.TonhubConnector({ network: "sandbox" });
function getSession() {
    return __awaiter(this, void 0, void 0, function () {
        var session;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0: return [4 /*yield*/, exports.connector.createNewSession({
                        name: 'NFT Web3TON',
                        url: 'https://web3ton.pro/'
                    })];
                case 1:
                    session = _a.sent();
                    console.log(session);
                    return [2 /*return*/, session];
            }
        });
    });
}
exports.getSession = getSession;
function waitSession(sessionId, timeoutMin) {
    if (timeoutMin === void 0) { timeoutMin = 5; }
    return __awaiter(this, void 0, void 0, function () {
        var session;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0: return [4 /*yield*/, exports.connector.awaitSessionReady(sessionId, timeoutMin * 60 * 1000)];
                case 1:
                    session = _a.sent();
                    console.log(session);
                    return [2 /*return*/, session];
            }
        });
    });
}
exports.waitSession = waitSession;
function sendCoins(to, amount, message, sessionSeed, appPublicKey, timeoutMin) {
    if (timeoutMin === void 0) { timeoutMin = 5; }
    return __awaiter(this, void 0, void 0, function () {
        var request, response, externalMessage;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    request = {
                        seed: sessionSeed,
                        appPublicKey: appPublicKey,
                        to: to,
                        value: amount.toString(),
                        timeout: timeoutMin * 60 * 1000,
                        text: message
                    };
                    return [4 /*yield*/, exports.connector.requestTransaction(request)];
                case 1:
                    response = _a.sent();
                    console.log(response);
                    if (response.type === 'rejected') {
                        // Handle rejection
                    }
                    else if (response.type === 'expired') {
                        // Handle expiration
                    }
                    else if (response.type === 'invalid_session') {
                        // Handle expired or invalid session
                    }
                    else if (response.type === 'success') {
                        externalMessage = response.response;
                        console.log(externalMessage);
                    }
                    else {
                        throw new Error('Impossible');
                    }
                    return [2 /*return*/];
            }
        });
    });
}
exports.sendCoins = sendCoins;
