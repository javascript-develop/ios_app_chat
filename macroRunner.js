const rfb = require('rfb2');
const fs = require('fs');
const readline = require('readline');
const async = require('async');

const args = process.argv.slice(2);
if (args.length < 4) {
    console.error("Usage: node macroRunner.js <host> <port> <password> <commandsFile>");
    process.exit(1);
}

const [host, port, password, commandsFile] = args;

const client = rfb.createConnection({
    host: host,
    port: parseInt(port),
    password: password
});

client.on('connect', () => {
    console.log('Connected to the VNC server');
    runMacro(commandsFile);
});

client.on('error', (err) => {
    console.error('Error:', err.message);
    process.exit(1);
});

function mouseMove(x, y) {
    client.pointerEvent(x, y, 0);
}

function mouseClick(button) {
    const buttonMask = button === 'sx' ? 1 : 4;
    client.pointerEvent(0, 0, buttonMask);
    client.pointerEvent(0, 0, 0);
}

function typeKey(key) {
    for (const char of key) {
        client.keyEvent(char.charCodeAt(0), 1);
        client.keyEvent(char.charCodeAt(0), 0);
    }
}

function runMacro(commandsFile) {
    const rl = readline.createInterface({
        input: fs.createReadStream(commandsFile),
        output: process.stdout,
        terminal: false
    });

    const queue = async.queue((command, callback) => {
        const [action, value] = command.split(':');
        switch (action) {
            case 'mouseMove':
                const [x, y] = value.split(',').map(Number);
                mouseMove(x, y);
                break;
            case 'mouseClick':
                mouseClick(value);
                break;
            case 'type':
                typeKey(value);
                break;
            default:
                console.log(`Unknown action: ${action}`);
                break;
        }

        setTimeout(callback, 500);
    }, 1);

    rl.on('line', (line) => {
        queue.push(line.trim());
    });

    rl.on('close', () => {
        queue.drain(() => {
            console.log('Macro execution completed');
            client.end();
        });
    });
}
