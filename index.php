<div id="wrapper" class="wrapper">
    <div class="container">
        <h1>Welcome</h1>
        <div id="error" class="error"></div>
        <form class="form" id="signup">
            <input type="email" id="email" placeholder="Username" required>
            <input type="password" id="password" placeholder="Password" required>
            <button type="button" id="login-button" class="loginbutton">Login</button>
            <button type="button" id="signup-button" class="loginbutton">Sign Up</button><br>
            <button type="button" id="google-login" class="google-button">
            <span class="google-button__icon">
                <svg viewBox="0 0 366 372" xmlns="http://www.w3.org/2000/svg"><path d="M125.9 10.2c40.2-13.9 85.3-13.6 125.3 1.1 22.2 8.2 42.5 21 59.9 37.1-5.8 6.3-12.1 12.2-18.1 18.3l-34.2 34.2c-11.3-10.8-25.1-19-40.1-23.6-17.6-5.3-36.6-6.1-54.6-2.2-21 4.5-40.5 15.5-55.6 30.9-12.2 12.3-21.4 27.5-27 43.9-20.3-15.8-40.6-31.5-61-47.3 21.5-43 60.1-76.9 105.4-92.4z" id="Shape" fill="#EA4335"/><path d="M20.6 102.4c20.3 15.8 40.6 31.5 61 47.3-8 23.3-8 49.2 0 72.4-20.3 15.8-40.6 31.6-60.9 47.3C1.9 232.7-3.8 189.6 4.4 149.2c3.3-16.2 8.7-32 16.2-46.8z" id="Shape" fill="#FBBC05"/><path d="M361.7 151.1c5.8 32.7 4.5 66.8-4.7 98.8-8.5 29.3-24.6 56.5-47.1 77.2l-59.1-45.9c19.5-13.1 33.3-34.3 37.2-57.5H186.6c.1-24.2.1-48.4.1-72.6h175z" id="Shape" fill="#4285F4"/><path d="M81.4 222.2c7.8 22.9 22.8 43.2 42.6 57.1 12.4 8.7 26.6 14.9 41.4 17.9 14.6 3 29.7 2.6 44.4.1 14.6-2.6 28.7-7.9 41-16.2l59.1 45.9c-21.3 19.7-48 33.1-76.2 39.6-31.2 7.1-64.2 7.3-95.2-1-24.6-6.5-47.7-18.2-67.6-34.1-20.9-16.6-38.3-38-50.4-62 20.3-15.7 40.6-31.5 60.9-47.3z" fill="#34A853"/></svg>
            </span>
            <span class="google-button__text">Sign in with Google</span>
            </button>
            <div class="nosignin"><a href="#" id="nosignin">Continue without signing in</a></div>
        </form>
        <div id="rolechooser" class="role hidden">
          <button type="button" id="hero-button" class="loginbutton">I'm a hero</button>
          <button type="button" id="villain-button" class="loginbutton">I'm a villain</button><br>
        </div>
        <div id="display-characters" class="characters-wrapper hidden">
            <div id="ordinary" class="characters">
                <p><strong>Totally ordinary people:</strong></p>
                <ul id="ordinaries-list">
                </ul>
            </div>
            <div id="heroes" class="characters">
                <p><strong>Heroes:</strong></p>
                <ul id="heroes-list">
                </ul>
            </div>
            <div id="villains" class="characters">
                <p><strong>Villains:</strong></p>
                <ul id="villains-list">
                </ul>
            </div>
        </div>
    </div>
</div>

<script>

var client = Kinvey.init({
    appKey: 'kid_rk7NMn57z',
    appSecret: '3ecc483bd0864882b0c69965030961c6'
});

const heroRoleId = '0278b7bf-749f-453f-9b74-4a0b2afcfcff',
    villainRoleId = '1707214d-5c2f-436c-82d4-6e198749251d';

// for the sake of simplicity in a sample app, I am always logging the current active user out when the page is loaded
var promise = Kinvey.User.logout();

// sign up a new user with Kinvey authentication
document.getElementById('signup-button').addEventListener('click', function(event) {
    // If you want to validate these inputs before sending them to the backend you should do that here
    var user = new Kinvey.User();
    var promise = user.signup({
        username: document.getElementById('email').value,
        password: document.getElementById('password').value
    })
    .then(function(user) {
        loginSuccess();
        console.log(user);
    })
    .catch(function(error) {
        // for the sake of simplicity, I'm just displaying any errors that the API sends me back
        document.getElementById('error').innerHTML = error.message;
    });
});

// login using the Kinvey authentication
document.getElementById('login-button').addEventListener('click', function(event) {
    var user = new Kinvey.User();
    var promise = user.login({
        username: document.getElementById('email').value,
        password: document.getElementById('password').value
    })
    .then(function(user) {
        loginSuccess();
        console.log(user);
    })
    .catch(function(error) {
        document.getElementById('error').innerHTML = error.message;
    });
});

// sign up or log in with Google authentication
document.getElementById('google-login').addEventListener('click', function(event) {
    var promise = Kinvey.User.loginWithMIC(window.location.href);
    promise.then(function onSuccess(user) {
        loginSuccess();
        console.log(user);
    }).catch(function onError(error) {
        document.getElementById('error').innerHTML = error.message;
    });
});

// log in an implicit user (i.e. anonymous) that defaults to the all users role
document.getElementById('nosignin').addEventListener('click', function () {
    loginSuccess();
    document.getElementById('rolechooser').classList.add('fadeout');
    var promise = Kinvey.User.signup()
    .then(function(user) {
        loadData();
    }).catch(function(error) {
        console.log(error);
    });
});

// just in case, remove the other role first then pass the hero role id to assign the role
document.getElementById('hero-button').addEventListener('click', function(event) {
    var userid = Kinvey.User.getActiveUser(client)._id,
        promise = Kinvey.CustomEndpoint.execute('deleteRole', {
        userid: userid,
        roleid: villainRoleId
    })
    .then(function(response) {
        setRole(heroRoleId);
    })
    .catch(function(error) {
        console.log(error);
    });
});

// // just in case, remove the other role first then pass the villain role id to assign the role
document.getElementById('villain-button').addEventListener('click', function(event) {
    var userid = Kinvey.User.getActiveUser(client)._id,
        promise = Kinvey.CustomEndpoint.execute('deleteRole', {
        userid: userid,
        roleid: heroRoleId
    })
    .then(function(response) {
        setRole(villainRoleId);
    })
    .catch(function(error) {
        console.log(error);
    });
        
});

// change some styles when a user log in succeeds
function loginSuccess() {
    var rolechooser = document.getElementById('rolechooser');

    document.getElementById('signup').classList.add('fadeout');
    document.getElementById('wrapper').classList.add('form-success');
    rolechooser.classList.remove('hidden');    
    rolechooser.classList.add('fadein');
}

// set the user role via the REST API (not available in SDK at the moment)
function setRole(roleid) {
    var userid = Kinvey.User.getActiveUser(client)._id,
        promise = Kinvey.CustomEndpoint.execute('addRole', {
            userid: userid,
            roleid: roleid
        })
        .then(function(response) {
            console.log(response);
            document.getElementById('rolechooser').classList.add('fadeout');
            loadData();
        })
        .catch(function(error) {
            console.log(error);
        });
}

// load data from 3 collections - one with all user access, one with hero only and one with villain only
function loadData() {
    var ordinary_ds = Kinvey.DataStore.collection('ordinary-people'),
        heroes_ds = Kinvey.DataStore.collection('heroes'),
        villains_ds = Kinvey.DataStore.collection('villains');
    ordinary_ds.pull()
    .then(function(ordinaries) {
        var el = document.getElementById('ordinaries-list'),
        chrList = '';
        ordinaries.forEach(function(ordinary) {
            chrList += '<li>' + ordinary.name + '</li>';
        });
        el.innerHTML = chrList;
        displayCharacters();
    })
    .catch(function(error) {
        console.log(error);
    });
    heroes_ds.pull()
    .then(function(heroes) {
        var el = document.getElementById('heroes-list'),
        chrList = '';
        heroes.forEach(function(hero) {
            chrList += '<li>' + hero.hero_name + '</li>';
        });
        el.innerHTML = chrList;
        displayCharacters();
    })
    .catch(function(error) {
        console.log(error);
        if (error.code == 401) {
            var el = document.getElementById("heroes-list").innerHTML = '<li>Unauthorized</li>'
        }
    });
    villains_ds.pull()
    .then(function(villains) {
        var el = document.getElementById('villains-list'),
            chrList = '';
        villains.forEach(function(villain) {
            chrList += '<li>' + villain.villain_name + '</li>';
        });
        el.innerHTML = chrList;
        displayCharacters();
    })
    .catch(function(error) {
        console.log(error);
        if (error.code == 401) {
            var el = document.getElementById("villains-list").innerHTML = '<li>Unauthorized</li>'
        }
    });
}

// just a simple utility to determine if the lists are already displayed and display them
function displayCharacters() {
    display = document.getElementById('display-characters');
    if (display.classList.contains('hidden')) {
        display.classList.remove('hidden');
        display.classList.add('fadein');
    }
}
</script>


<style>

/* most styles borrowed from
https://codepen.io/Lewitje/pen/BNNJjo?q=login&order=popularity&depth=everything&show_forks=false
*/
@import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300);
* {
  -webkit-box-sizing: border-box;
          box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-weight: 300;
}
body {
  font-family: 'Source Sans Pro', sans-serif;
  color: white;
  font-weight: 300;
}
body ::-webkit-input-placeholder {
  /* WebKit browsers */
  font-family: 'Source Sans Pro', sans-serif;
  color: white;
  font-weight: 300;
}
body :-moz-placeholder {
  /* Mozilla Firefox 4 to 18 */
  font-family: 'Source Sans Pro', sans-serif;
  color: white;
  opacity: 1;
  font-weight: 300;
}
body ::-moz-placeholder {
  /* Mozilla Firefox 19+ */
  font-family: 'Source Sans Pro', sans-serif;
  color: white;
  opacity: 1;
  font-weight: 300;
}
body :-ms-input-placeholder {
  /* Internet Explorer 10+ */
  font-family: 'Source Sans Pro', sans-serif;
  color: white;
  font-weight: 300;
}
.wrapper {
  background: #50a3a2;
  background: -webkit-gradient(linear, left top, right bottom, from(#50a3a2), to(#53e3a6));
  background: linear-gradient(to bottom right, #50a3a2 0%, #53e3a6 100%);
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 450px;
  margin-top: -200px;
  overflow: hidden;
}
.wrapper.form-success .container h1 {
  -webkit-transform: translateY(85px);
          transform: translateY(85px);
}
.container {
  max-width: 600px;
  margin: 0 auto;
  padding: 80px 0;
  height: 400px;
  text-align: center;
}
.container h1 {
  font-size: 40px;
  -webkit-transition-duration: 1s;
          transition-duration: 1s;
  -webkit-transition-timing-function: ease-in-put;
          transition-timing-function: ease-in-put;
  font-weight: 200;
}
form {
  padding: 20px 0;
  position: relative;
  z-index: 2;
}
form input {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  outline: 0;
  border: 1px solid rgba(255, 255, 255, 0.4);
  background-color: rgba(255, 255, 255, 0.2);
  width: 250px;
  border-radius: 3px;
  padding: 10px 15px;
  margin: 0 auto 10px auto;
  display: block;
  text-align: center;
  font-size: 18px;
  color: white;
  -webkit-transition-duration: 0.25s;
          transition-duration: 0.25s;
  font-weight: 300;
}
form input:hover {
  background-color: rgba(255, 255, 255, 0.4);
}
form input:focus {
  background-color: white;
  width: 300px;
  color: #53e3a6;
}
.loginbutton {
  -webkit-appearance: none;
     -moz-appearance: none;
          appearance: none;
  outline: 0;
  background-color: white;
  border: 0;
  padding: 10px 15px;
  color: #53e3a6;
  border-radius: 3px;
  width: 125px;
  cursor: pointer;
  font-size: 18px;
  -webkit-transition-duration: 0.25s;
          transition-duration: 0.25s;
}
form button:hover {
  background-color: #f5f7f9;
}
.fadeout {
  visibility: hidden;
  opacity: 0;
  transition: visibility 0s .5s, opacity .5s linear;
}

.fadeout2 {
  visibility: hidden;
  opacity: 0;
  transition: visibility 0s .5s, opacity .5s linear;
}

@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
.fadein {
  opacity: 0;
  animation:fadeIn ease-in 1;
  animation-fill-mode: forwards;
  animation-duration: 0.5s;
  animation-delay: 0.5s;
}

.error {
    color: #fcd6e6;
}

.google-button {
  height: 40px;
  border-width: 0;
  background: white;
  color: #737373;
  border-radius: 5px;
  white-space: nowrap;
  -webkit-box-shadow: 1px 1px 0px 1px rgba(0, 0, 0, 0.05);
          box-shadow: 1px 1px 0px 1px rgba(0, 0, 0, 0.05);
  -webkit-transition-property: background-color, -webkit-box-shadow;
  transition-property: background-color, -webkit-box-shadow;
  transition-property: background-color, box-shadow;
  transition-property: background-color, box-shadow, -webkit-box-shadow;
  -webkit-transition-duration: 150ms;
          transition-duration: 150ms;
  -webkit-transition-timing-function: ease-in-out;
          transition-timing-function: ease-in-out;
  padding: 0;
  margin-top: 4px;
  width: 254px;
}

.google-button__icon {
  display: inline-block;
  vertical-align: middle;
  margin: 8px 0 8px 8px;
  width: 18px;
  height: 18px;
  -webkit-box-sizing: border-box;
          box-sizing: border-box;
}

.google-button__icon--plus {
  width: 27px;
}

.google-button__text {
  display: inline-block;
  vertical-align: middle;
  padding: 0 24px;
  font-size: 14px;
  font-weight: bold;
  font-family: 'Roboto',arial,sans-serif;
}

.role {
  margin-top:-150px;
}

.characters-wrapper {
  margin-top:-75px;
}

.characters {
  width:30%;
  margin:1.5%;
  float:left;
}

.hidden {
  display:none;
}

.nosignin {
  margin-top:20px;
  font-size: .8em;
}
</style>
