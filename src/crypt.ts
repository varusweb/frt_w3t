import nacl from "tweetnacl";
import utils from "tweetnacl-util";


const secretEncoded = "xSHIUY64SRKr3Da5+gElPpKxVQWalnFcuEHyaKw0oFwHmBssXeVZzW2GMZ/PvuEDaTqGGxGTlirumesTceDEMQ==";

export function sign(input: string) {
    const secretKey = utils.decodeBase64(secretEncoded);
    // const keyPair = nacl.sign.keyPair.fromSecretKey(secretKey);

    const signedBytes = nacl.sign(utils.decodeUTF8(input), secretKey);
    const signedString = utils.encodeBase64(signedBytes);

    return signedString;
}
