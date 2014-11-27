$("#botLoading1").click(function () {
    $.MetroLoading({
        title: "Loading",
        content: "Please wait 5 seconds",
        timeout: 5000,
        img: "static/img/load1.gif"
    }, function () {
        alert("Finish!")
    })
});
$("#botLoading2").click(function () {
    $.MetroLoading({
        title: "Load is different",
        content: "Now, wait 4 seconds.",
        img: "static/img/load3.gif",
        timeout: 4000
    }, function () {
        alert("Finish!")
    })
});
$("#botLoading3").click(function () {
    $.MetroLoading({
        title: "Loading",
        content: "Now, wait 4 seconds.. custom loading gif",
        timeout: 4000,
        img: "static/img/load2.gif"
    }, function () {
        alert("Finish!")
    })
});
$("#botLoading4").click(function () {
    $.MetroLoading({
        title: "With no Image",
        content: "Now, wait 4 seconds.",
        timeout: 4000
    }, function () {
        alert("Finish!")
    })
});
$("#botLoadingSpecial1").click(function () {
    $.MetroLoading({
        title: "Now we are loading...",
        content: "Please wait until the process finish...",
        timeout: 9000,
        special: true
    }, function () {
        alert("Finish!")
    })
});
$("#botLoadingSpecial2").click(function () {
    $.MetroLoading({
        title: "Working for you...",
        content: "Please rate 5 stars if you like it!",
        img: "static/img/load3.gif",
        timeout: 9000,
        special: true
    }, function () {
        alert("Finish!")
    })
});
$("#botLoadingSpecial3").click(function () {
    $.MetroLoading({
        title: "This loading will never stop.",
        content: "Until you call 'MetroUnLoading' function!",
        timeout: 1000000,
        special: true
    }, function () {
        alert("Finish!")
    });
    setTimeout(function () {
        var a = "";
        $("#MetroUnloadingButton").show();
        $("body").append(a)
    }, 7000)
});

function Close() {
    MetroUnLoading();
    $("#MetroUnloadingButton").hide()
}

$("#botSmallPic1 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "With picture, timeout 4 seconds, no icon and custom color.",
        timeout: 4000,
        color: "#ec008c ",
        img: "static/img/pic1.png "
    })
});
$("#botSmallPic2 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "Different picture, timeout 4 seconds, no icon and custom color.",
        color: "#92278f",
        timeout: 4000,
        img: "static/img/pic2.png"
    })
});
$("#botSmallPic3 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "Different picture, timeout 4 seconds, no icon and custom color.",
        color: "#000000",
        timeout: 4000,
        img: "static/img/pic3.png"
    })
});
$("#botSimple1 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "No Picture, 4 seconds timeout, no icon and custom color.",
        color: "#1ba1e2",
        timeout: 4000
    })
});
$("#botSimple2 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "No Picture, 4 seconds timeout, no icon and custom color.",
        color: "#a4c400 ",
        timeout: 4000
    })
});
$("#botSimple3 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "No Picture, 4 seconds timeout, no icon and custom color.",
        color: "#ec008c ",
        timeout: 4000
    })
});
$("#botSmallCustom1 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "Picture, No timeout, with phone icon, custom color and Callback on closing.",
        color: "#fa6800 ",
        img: "static/img/pic2.png ",
        icon: "static/img/iphone.png "
    }, function () {
        alert("Hi there, you are calling a callback function !")
    })
});
$("#botSmallCustom2 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "No Picture, No timeout, with cloud icon, custom color and Callback function ",
        color: "#a4c400 ",
        icon: "static/img/cloud.png "
    }, function () {
        alert("Hi there, you are calling a callback function !")
    })
});
$("#botSmallCustom3 ").click(function () {
    $.smallBox({
        title: "Small Information box ",
        content: "No Picture, 4 seconds timeout, no icon, custom color and Callback function ",
        color: "#1ba1e2",
        icon: "static/img/calendar.png"
    }, function () {
        alert("Hi there, you are calling a callback function!")
    })
});

$("#BigBox1 ").click(function () {
    $.bigBox({
        title: "Big Information box ",
        content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Ut enim ad minim veniam, quis nostrud exercitation ullamco.Lorem ipsum dolor sit amet.",
        color: "#fa6800 ",
        timeout: 8000,
        img: "static/img/members.png ",
        number: "1 "
    })
});
$("#BigBox2 ").click(function () {
    $.bigBox({
        title: "Big Information box ",
        content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Ut enim ad minim veniam, quis nostrud exercitation ullamco.Lorem ipsum dolor sit amet.",
        color: "#d80073 ",
        timeout: 8000,
        img: "static/img/iphone.png ",
        number: "23 "
    })
});
$("#BigBox3 ").click(function () {
    $.bigBox({
        title: "Big Information box ",
        content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Ut enim ad minim veniam, quis nostrud exercitation ullamco.Lorem ipsum dolor sit amet.",
        color: "#1ba1e2",
        timeout: 8000,
        img: "static/img/cloud.png",
        number: "3"
    })
});
$("#BigBoxCall1 ").click(function () {
    $.bigBox({
        title: "Big Information box ",
        content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Ut enim ad minim veniam, quis nostrud exercitation ullamco.Lorem ipsum dolor sit amet.",
        color: "#f472d0 ",
        img: "static/img/cloud.png ",
        number: "5 "
    })
});
$("#BigBoxCall2 ").click(function () {
    $.bigBox({
        title: "Big Information box ",
        content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Ut enim ad minim veniam, quis nostrud exercitation ullamco.Lorem ipsum dolor sit amet.",
        color: "#1ba1e2",
        img: "static/img/iphone.png",
        number: "2"
    })
});
$("#BigBoxCall3 ").click(function () {
    $.bigBox({
        title: "Big Information box ",
        content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Ut enim ad minim veniam, quis nostrud exercitation ullamco.Lorem ipsum dolor sit amet.",
        color: "#6a00ff",
        img: "static/img/members.png",
        number: "7"
    })
});
$("#botMessage1 ").click(function () {
    $.MetroMessageBox({
        title: "Metro Notifications !! !",
        content: "This is a confirmation box.You can change the color of the buttons if you want.",
        NormalButton: "#232323",
        ActiveButton: "# 0050ef "
    })
});
$("#botMessage2 ").click(function () {
    $.MetroMessageBox({
        title: "Metro Notifications !! !",
        content: "A lot of text !! !, Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Ut enim ad minim veniam, quis nostrud exercitation ullamco.",
        NormalButton: "#232323",
        ActiveButton: "#a20025 "
    })
});
$("#botMessage3 ").click(function () {
    $.MetroMessageBox({
        title: " < span style = 'color: #f472d0;' > Metro PINK !! < /span>",
        content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.",
        NormalButton: "#232323",
        ActiveButton: "#f472d0"
    })
});
$("#botMultiMessage1").click(function () {
    $.MetroMessageBox({
        title: "Metro Notifications!!!",
        content: "This is a confirmation box. You can change the color of the buttons if you want.",
        NormalButton: "#232323",
        ActiveButton: "#008a00",
        buttons: "[No][Yes]"
    })
});
$("#botMultiMessage2").click(function () {
    $.MetroMessageBox({
        title: "Metro Notifications!!!",
        content: "This is a confirmation box. You can change the color of the buttons if you want.",
        NormalButton: "#232323",
        ActiveButton: "#008a00",
        buttons: "[Cancel][No][Yes]"
    })
});
$("#botMultiMessage3").click(function () {
    $.MetroMessageBox({
        title: "Metro Notifications!!!",
        content: "This is a confirmation box. You can change the color of the buttons if you want.",
        NormalButton: "#232323",
        ActiveButton: "#008a00",
        buttons: "[Need?][You][Do][Buttons][Many][How]"
    })
});
$("#botRecursive1").click(function () {
    $.MetroMessageBox({
        title: "Metro Notifications!!!",
        content: "Recursive Messagebox... Just press the button ok?",
        NormalButton: "#232323",
        ActiveButton: "#e51400",
        buttons: "[C'mon bro...]"
    }, function () {
        $.MetroMessageBox({
            title: "Metro Notifications!!!",
            content: "You can call another one if you want. <br/ > Wait a second...the button has a different color ? ",
            NormalButton: "#232323",
            ActiveButton: "#f0a30a ",
            buttons: " [Thank You !! ]"
        })
    })
});
$("#botRecursive2 ").click(function () {
    $.MetroMessageBox({
        title: "Metro Notifications !! !",
        content: "Recursive Messagebox...Just press the button ok ? ",
        NormalButton: "#232323",
        ActiveButton: "#e51400 ",
        buttons: " [This is going to be Legend...]"
    }, function () {
        $.MetroMessageBox({
            title: "Metro Notifications !! !",
            content: "Recursive Messagebox...Just press the button ok ? ",
            NormalButton: "#232323",
            ActiveButton: "#f0a30a ",
            buttons: " [wait for it...]"
        }, function () {
            $.MetroMessageBox({
                title: "Metro Notifications !! !",
                content: "This is a confirmation box.You can change the color of the buttons if you want.",
                NormalButton: "#232323",
                ActiveButton: "#008a00 ",
                buttons: " [..........dary]"
            })
        })
    })
});
$("#botRecursive3 ").click(function () {
    $.MetroMessageBox({
        title: "Metro Notifications !! !",
        content: "Show the name of the button pressed in a common alert.",
        NormalButton: "#232323",
        ActiveButton: "#008a00 ",
        buttons: " [Need ? ][You][Do][Buttons][Many][How]"
    }, function (a) {
        alert("Hey there...you presss " + a + " !! !")
    })
});
$("#botInputBox1 ").click(function(){
        $.MetroMessageBox({title:"Metro Notifications !! !",
        content:"Please enter your user name ",
        NormalButton:"#232323",
        ActiveButton:"#1ba1e2 ",
        buttons:" [Accept]",
        input:"text ",
        placeholder:"Enter your user name "},
        function(b,a){
            alert(b+""+a)})});$("#botInputBox2 ").click(function(){
            
        $.MetroMessageBox({
            title:"Login form ",
            content:"Please enter your user name ",
            NormalButton:"#232323",
            ActiveButton:"#1ba1e2 ",
            buttons:"[Cancel][Accept]",
            input:"text ",
            placeholder:"Enter your user name "},
            function(b,a){if(b=="Cancel "){
                alert("Why do you cancel that ? ");
            return 0}Value1=a.toUpperCase();ValueOriginal=a;
            
            $.MetroMessageBox({
            title:Value1,
            content:"And now your password.",
            NormalButton:"#232323",
            ActiveButton:"#1ba1e2 ",
            buttons:" [Login]",
            input:"password ",
            placeholder:"Password "},
            function(d,c){
                alert("Username : "+ValueOriginal+"and your password is : "+c)})})});
               $("#botInputBox3 ").click(function(){
                                                  
            $.MetroMessageBox({
            title:"Metro Notifications !! !",
            content:"You can even create a group of options.",
            NormalButton:"#232323",
            ActiveButton:"#1ba1e2 ",
            buttons:" [I choose]",
            input:"select ",options:" [Costa Rica][United States][Autralia][Spain]"},
            function(b,a){
            alert(b+""+a)})});