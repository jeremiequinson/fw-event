# FW Event

Fw event est un projet de gestion des évènement interne organiser.

## Description

Cette application permet à un utilisateur de créer un évènement et d'y inviter d'autres utilisateurs.

Un évènement peut s'etendre d'une heures a plusieurs jours.

Le contenu d'évènement est libre de choix.

Les personnes invitées peuvent confirmer leurs présences ou non a l'evennement.

Une fois l'évènement déroulé, les utilisateurs ont la possibilité de laisser un commentaire avec une note.

Les specifications se trouvent dans le dossier doc/spec.

## Getting Started

This project is supplied with a "ready to use" docker container.
However, to avoid permission issue, you must to run docker or execute commands using your UID. 

So you'll have to provide an environment var before running docker and commands.

To start the containers
`$ CURRENT_UID=$(id -u):$(id -g) docker-compose up`

To run command inside container api
`$ CURRENT_UID=$(id -u):$(id -g) docker-compose exec api bin/console cache:clear`

**Warning:** As I wanted to provide a "ready to use" project, I load fixtures on container start.
Please keep in mind that a docker-compose up will reset fixtures and database.

### Start project

Start the project
`$ CURRENT_UID=$(id -u):$(id -g) docker-compose up`.

If you are running on Linux, everything should be fine. If you are running on Window or Mac, pray.

Once everything is started and ready (you will see a message `====== READY ======`), the API doc will be available at the address http://127.0.0.1:8001/api.


## The project

### Stack

Here is a quick description of the stack :
- Symfony 4
- Doctrine / Doctrine migration
- API Plateform
- Authentication with JWT

### Entities

Entities follow the model. There are: User, Event, Invitation, Place and Comment.
I added another entity Media to handle user avatars properly.

### Authentication and security

Authentication is based on JWT authentication. Full authentication is required to access on every url prefixed by /api.

As Registration and Login are the only endpoints not prefixed by /api, they don't require authentication.

To create an account, use the endpoint POST /register

#### Register

Example: 
```
$ curl -X POST "http://127.0.0.1:8001/register" -H "accept: application/json" -H "Content-Type: application/json" -d "{ \"username\": \"jackthedog99\", \"email\": \"jackthedog99@yopmail.com\", \"plainPassword\": \"testtest\"}"
```

Note this endpoint required "json" instead of "json-ld" as almost all endpoints of the API.

#### Login

Then, to get a token and user data, use endpoint POST /login_check

Example: 
```
$ curl -X POST "http://127.0.0.1:8001/login_check" -H "accept: application/ld+json" -H "Content-Type: application/ld+json" -d "{ \"username\": \"jacklechien99\", \"password\": \"testtest\"}"
```

This request return  an object containing a token and a user object:
```
{
"token":"xxxxx",
"user": {
	"@context": "/api/contexts/User",
	"@id": "/api/users/43",
	"@type": "User",
	"id": 43,
	"email": "jeremie.quinson+2@gmail.com",
	"roles": ["ROLE_USER"],
	"username": "jquinson",
	"avatar": null,
	"avatarUrl": null,
	"createdAt": "2019-05-09T18:44:06+00:00"
	}
}
```

Every authenticated request must have a header "Authorization" with content "Bearer XXXXXX" where XXXX is the token.

The token is reusable until is ttl has expired (3600s)

#### Roles

Every authenticated user has role "ROLE_USER". I handle additional "ROLE_ADMIN" to test some functionality in "access_control" in
API. For now it has to be added manually.


#### Access control

Anonymous users can register and login.

Authenticated users can create every type of entities (Event, Invitation, User, Place, Comment, Media...). However, they only can invite users for the events they organize.

Authenticated users can only update and delete Event and Comment they created. They also can update there profile and upload an avatar.

Authenticated users can update and delete every place, there is no restriction.

Authenticated users can only confirm invitation they received. They can also edit invitation of there own event.

Admin users can edit and delete every entites, they have super power.


### Specifications

#### About the API

API is generated by API Plateform and customised to fit the specifications.
So every endpoints of the API (excepted some endpoint like /register) accept "application/json" and return "application/ld+json".

This format provide some contextual data particularly:
-  "@type" (hydra;collection, name of the Entity...
- "@id" (Endpoint of the entity. Ex: "@id": "/api/users/3" for user with ID 3)

In POST/PUT method, when we want to post entity relation, we have to specify this "@id".
Here is an exemple of a POST event request:
```
$ curl -X POST "http://127.0.0.1:8001/api/events" -H "accept: application/ld+json" -H "Authorization: Bearer XXXXXXXX" -H "Content-Type: application/json" -d "{ \"name\": \"sdf\", \"description\": \"sdf\", \"startAt\": \"2019-05-09T17:10:34.115Z\", \"endAt\": \"2019-05-10T17:10:34.115Z\", \"place\": \"/api/places/2\"}"
```

The complete API documentation is available in http://127.0.0.1:8001/api

#### Collections

Every collection endpoints has pagination. Page can be sent with parameter "page".
By default, there are 50 items per page. It can be changed with query parameter: "itemsPerPage".

Collections have set of filter to search and sort data.

#### Place

User can get the list of places and get one specified item using the id.

User can create, edit and delete any place by using these endpoints:
POST /api/places
PUT /api/places/:id
DELETE /api/places/:id

**Constraints**

A place cannot be deleted if it's associated to an event.
A place cannot have a name if it's already used.

#### Event

User can get the list of events and get one specified item using the id.
It's possible to filter events by date, search by organizer or event place.

There are 2 additionals endpoints to fetch all comments and participants of a specified event.
/api/events/:id/comments
/api/events/:id/participants

User can create an event and specify a place.
Ex of payload:
```
{
  "name": "Amazing event",
  "description": "Description of amazing event",
  "startAt": "2019-05-09T17:10:34.115Z",
  "endAt": "2019-05-10T17:10:34.115Z",
  "place": "/api/places/2"
}
```

But the user can also create an event and a new place by embedding a place payload.
Ex of payload:
```
{
  "name": "Amazing event",
  "description": "Description of amazing event",
  "startAt": "2019-05-09T17:10:34.115Z",
  "endAt": "2019-05-10T17:10:34.115Z",
  "place": {
	  "name": "Place name",
	  "streetNumber": "99",
	  "city": "City",
	  "streetName": "name of the street",
	  "postalCode": "35000",
	  "country": "FR"
	}
  }
}
```
Of course, if the place "name" already used, API will return a 400 Bad Request.

An user can also update or delete an event if the user is the owner.

**Constraints**

An event must have a "startAt" < "endAt"

**Note**

Delete an event will delete all associated comments and invitation


#### Invitation

A user can fetch invitations list, a specified invitation or invitations list for a specified event.
It's also possible to get all invitations and events for a specified user (using /api/users/:id/invitations)

Invitations (for all collection endpoint) can be filtered, espacially with the criterias: "isExpired" and "confirmed" which are very usefull.

A user can post, edit and delete an invitation.

The only property which can be edited using PUT method is the expiration date.
To confirm an invitation there is a dedicated endpoint: /api/invitations/:id/confirm.

**Constraints**

- An event must be owned by the user to POST, PUT and DELETE event. Otherwise API return a 400 status code.
- If the user is already invited for the event, API return a 400 status code.
- A user cannot invit for an owned event
- Expiration date must be grather than today in POST method
- If an invitation has expired, user cannot confirm and an error is thrown

#### Comment

A user can fetch a list of comment for a specified event.

It's also possible to POST, PUT and DELETE a comment for a specified event.

**Constraints**

- Event must be finished to post a comment
- User can only edit or delete his own comments
- Rate must be a integer from 1 to 5
- An user cannot comment an event if there is no confirmed invitation


#### Users

User are created using the endpoint /register. So there is no way to create user using an authenticated endpoint.
It's possible to get users list or a specific user. Of course, password is excluded from serialization (event if it's encrypted).
User can edit his own profile. Admin can edit any profile.

To upload an avatar, user the endpoint POST /api/media and then update the user.

## Functional tests

Project is provided with functional tests. They run on a dedicated database running on a dedicated docker container.
To launch test, you just have to run ./runtests.sh.

`$ CURRENT_UID=$(id -u):$(id -g) docker-compose exec api ./runtests.sh`

## How could I improve this project ?

Some stuff were very touchy and challenging. I tried to do everything listed in specification (and more). But here is a list of things I should add/improve in this project:
- Create a custom filter for "Past/non past" elements based on property name specified in the conf
- Some endpoint exclude property for denormalization in PUT, POST or custom endpoint. For exemple it's not possible to specify a "confirmed" value in Invitation POST/PUT. But the problem it's if we specify a "confirmed" prop in the body, the API return a 200 code. Hopefully the property is not handled but it would be nice to have an error in this case.
- I should use soft deletion (with the property deletedAt who is useless now)


