function main() {
    const readline = require('readline').createInterface({
    input: process.stdin,
    output: process.stdout,
});

readline.question('Please enter your name : ', input => {
    console.log(`Hello ${input}!`);
    console.log('Welcome to the world of Node.js');
    console.log('This is a simple program to demonstrate the use of readline module.');
    console.log('You can enter your name and the program will greet you.');
    console.log('You can also enter any other text and the program will echo it back to you.');
    console.log('This is a simple program to demonstrate the use of readline module.');
    console.log('You can enter your name and the program will greet you.');
    console.log('You can also enter any other text and the program will echo it back to you.');         
    readline.close();
});
 
}
DB_HOST=focusflow.cluster-c1aoiuwegh5u.eu-north-1.rds.amazonaws.com
main();
