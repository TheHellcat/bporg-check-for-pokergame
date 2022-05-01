# BetterPlace.org Donation Checker for Poker Bank

## Disclaimer

**IMPORTANT:** There is one thing the code in this project is not: Very good!

This has been laziely slapped together during the course of a day, with the sole purpose of being used once, on one event and only locally from my PC, not installed on a publically acessible webserver.

For all those - and more - reasons this code IS NOT BEST PRACTICE, sometimes it's even not how you should do things at all.

**THIS IS NOT A GOOD EXAMPLE OF HOW TO WRITE (GOOD) CODE**

However, I have been asked if I could make the code of my BetterPlace check thingo public, so people can look at it, how it*s done.

So here you go. Don't complain :-P


## What is it, what does it do?

This is a simple web-app that checks an event (project or fundraiser) on BetterPlace.org and parses the messages given for any donation to determine for which player of a local poker game the donation should be counted to be added to their stash of poker chips on the bank.

This was a charity event, we did, where we played poker and viewers of the events stream could influence the game by having their donations count towards the poker chips amount each player got at the beginning of a new round.

### How to try it out / install it, so it runs

This is a PHP Symfony application, so installation is pretty straight forward and done in like 3.14 seconds if you already know and/or have worked with Symfony or something similar:

- Make sure you got a working composer
- Run `composer install` in the projects root directory
- Create a `.env.local` file in the projects root directory and copy-past any settings from `.env` into it you want/need to change and then do change them to suite your setup (database connection, for example) - you could also directly edit the values in `.env`, but it's not recommended.
- Run `php bin\console doctrine:migrations:migrate` from the projects root directory to setup the database tables.
- Setup your local webserver you can access the application via it, the `htdocs`or `htroot` or w/e directory is the `/public`directory from the project files.
- Be happy, you're done :-)

### Use it / try it out

First you need to authenticate to be able to access the "bank overview" page:
- Go to `http://.../bporg/log-me-in/bank/1234567890` - put w/e you set as domain/hostname for the "..."

Now you can access the bank overview page via
- `http://.../bporg/overview/bpEventType/bpEventId` where for "bpEventType" you put "project" or "fundraiser" (depending what it exactely is, you want to access at BetterPlace) and "bpEventId" is the ID of it (you can get that from the URL of the BetterPlace event page, it's the number, that's in there, e.g. 40123 or such).

Everything on there should be pretty self-explanatory, it's a list of player balances (empty if you haven't parsed any messages, yet) with the option to book given out play-chips for each one.

#### Let it fetch'n'parse the messages:

- Go to `http://.../api/doc` and expand the GET request
- Click on "Try Out"
- Fill out the parameters, if it's a "project" or "fundraiser" and the BPB event ID
- Hit "Execute"
- Wait
- Eventually you will get a result that states "done: ture", unless something horrible happened and it crashed or w/e.
- Go back to the bank overview page and hit F5

Probably the list will still be empty as no matching string have been found to credit any messages to any player.

You can set the strings to be looked for and what player to credit it to in `\src\Service\BpOrgCheck\BpOrgHandler.php`.


## Points of interest / Looking at the code

The actual work happens in `\src\Service\BpOrgCheck\BpOrgHandler.php`.

Stuff in there called from stuff that's inside the `\src\Controller` directories.

Everything else should be able to be found on-demand with "control-clicking" in your IDE.

Have fun :-)
