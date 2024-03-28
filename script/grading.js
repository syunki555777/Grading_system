var selected = 0;
var progress = 0;
var numOfQuestions = 0;
var subject = "None";
var name = "None"
var sending = false;
//最初の認証処理
//初めに名前と被験者情報を取得します。その後、つづきからの場合は再開
$("title").html("評定者ツール(接続中)");

postData("submit.php", {mode: "authn"}).then(data => {
    if (data.status === "OK") {
        console.log("OKが返されました。");
        progress = parseInt(data.progress);
        numOfQuestions = parseInt(data.numOfQuestions);
        subject = data.subject;
        name = data.name;
        $("#subject").text(subject);
        updateQuestion();
        $("#top-bar h1").text(data.name+"さんの評定ツール");
        if(data.subject === "Done"){
            // Doneが帰ってきたら評定終了
            $("#NowLoading").html(name+"さん。</br>お疲れ様でした。すべての評定が完了しました。<br>またのご協力よろしくお願いします。");
        }else{
            $("#NowLoading").slideUp(function (){ $("#NowLoading").remove()});
        }
    }else if(data.status === "NG"){
        console.error("Errorが返されました。");
        $("#NowLoading").text("サーバーに問題が発生しました。");
        console.Error(data.error);
    }
}).catch(e=> {
    error("送信できませんでした")
    $("#NowLoading").text("接続できませんでした。");
});


$("#buttons > div").on("click",function (e){
    SelectReset();
    $(this).addClass("selection");
    selected = parseInt($(this).attr("id"));
    $("#submit").removeClass("invalid");
});

$("#error").hide();


$("#submit").on("click",function (e){
    sendSelected();
});

$(window).on("keyup",function(e){
    switch(e.keyCode){
        case 49:
            SelectReset();
            $("#1").addClass("selection");
            selected = 1;
            $("#submit").removeClass("invalid");
            break;
        case 50:
            SelectReset();
            $("#2").addClass("selection");
            selected = 2;
            $("#submit").removeClass("invalid");

            break;
        case 51:
            SelectReset();
            $("#3").addClass("selection");
            selected = 3;
            $("#submit").removeClass("invalid");

            break;
        case 52:
            SelectReset();
            $("#4").addClass("selection");
            selected = 4;
            $("#submit").removeClass("invalid");

            break;
        case 53:
            SelectReset();
            $("#5").addClass("selection");
            selected = 5;
            $("#submit").removeClass("invalid");

            break;
        case 13:
            sendSelected();
            break;
    }

})

function sendSelected(){
    if(selected === 0 || sending){
        return;
    }
    //送信処理
    //console.log(selected)
    sending = true;
    $("#submit").addClass("sending");
    postData("submit.php", {mode: "submit", progress: progress, subject:subject, name: name, label:selected}).then(data => {
        console.log(data);
        if (data.status === "OK") {
            console.log("OKが返されました。");
            progress = parseInt(data.progress);
            numOfQuestions = parseInt(data.numOfQuestions);
            subject = data.subject;
            name = data.name;
            $("#subject").text(subject);
            updateQuestion();
            $("#top-bar h1").text(data.name+"さんの評定ツール");

            if(subject === "Done"){
                //評定が終了したことを意味する
                $doc = $(`<div id=\"NowLoading\">${name}さん。</br>お疲れ様でした。すべての評定が完了しました。<br>またのご協力よろしくお願いします。</div>`).hide();
                $("body").prepend($doc);
                $("#NowLoading").slideDown();
            }

            SelectReset();
            $(".selection").removeClass("selection");
            sending = false;
            $(".sending").removeClass("sending")
        }else if(data.status === "NG"){
            console.error("Errorが返されました。");
            error("サーバーに問題が発生しました。");
            console.Error(data.error);
            sending = false;
            $(".sending").removeClass("sending")
        }
    }).catch(e=> {
        error("送信できませんでした。")
        console.error(e);
        sending = false;
        $(".sending").removeClass("sending")
    });
}

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
    $("#submit").addClass("invalid");
    $("#error").hide();
    selected = 0;
}

//postData('submit.php', { answer: 1 })
//   .then(data => {
//   console.log(data); // JSON data parsed by `response.json()` call
// });

function isIphone() {
    const userAgent = window.navigator.userAgent.toLowerCase();
    return userAgent.includes('iphone');
}

function addStylesheet(filename) {
    var head = document.head;
    var link = document.createElement("link");

    link.type = "text/css";
    link.rel = "stylesheet";
    link.href = filename;

    head.appendChild(link);
}

if(isIphone()){
    addStylesheet("iPhone.css");
}

function error(text){
    var errorEle = $("#error");
    errorEle.text(text);
    errorEle.show();
    errorEle.clearQueue();
    errorEle.delay(10000).queue(()=>{
        errorEle.hide();
    });
    console.log(text)
}

function updateQuestion(){
    $("#num").text(progress+"/"+numOfQuestions);
    $("title").html(name+"さんの評定ツール-"+Math.ceil((progress/numOfQuestions)*100)+"%");
    var ret = ( '000' + progress ).slice( -3 );
    $("#video video").attr("src","video/"+subject+"/"+subject+"_" + ret + ".mp4");

}