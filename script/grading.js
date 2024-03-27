var selected = 0;
$("#buttons > div").on("click",function (e){
    SelectReset();
    $(this).addClass("selection");
    selected = parseInt($(this).attr("id"));
});

$("#submit").on("click",function (e){
    if(selected === 0){
        return;
    }
    //送信処理
    console.log(selected)
    postData("submit.php",{test: 1}).then(data =>{
        console.log(data);
        if(data.status === "OK"){
            console.log("OKが返されました。")
            SelectReset();
        }
    });
    $(".selection").removeClass("selection");
});

function postData(url = '', data = {}) {
    // Default options are marked with *
    return fetch(url, {
        method: 'POST', // *GET, POST, PUT, DELETE, etc.
        mode: 'cors', // no-cors, *cors, same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        headers: {
            'Content-Type': 'application/json'
            // 'Content-Type': 'application/x-www-form-urlencoded',
        },
        redirect: 'follow', // manual, *follow, error
        referrerPolicy: 'no-referrer', // no-referrer, *client
        body: JSON.stringify(data) // body data type must match "Content-Type" header
    })
        .then(response => response.json()); // parses JSON response into native JavaScript objects
}

function SelectReset(){
    $(".selection").removeClass("selection");
    selected = 0;
}

//postData('submit.php', { answer: 1 })
//   .then(data => {
//   console.log(data); // JSON data parsed by `response.json()` call
// });