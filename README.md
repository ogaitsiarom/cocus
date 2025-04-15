# Setup Project
> ### Dependencies
> There is a docker folder within the project you need to run the following commands to have the infrastructure ready to go.
> - `docker compose -p cocus-challenge up --build -d`
> 
> This will create all needed to correctly start using this solution.
> 
> **NOTE**: You will need to edit the docker-compose.yml in order to add a user and password to the database. 

> ### Commands to configure Backend
> - .env file
>   - Copy `.env.example` to `.env`
>   - Ensure the `DATABASE_URL` is accondingly with what you set on docker-compose file
> - Dependencies
>   - `composer install`
> - Migrations
>   - `php bin/console doctrine:migrations:migrate`
> 
> - Create an user (you will need the user to login into the frontend)
>   - `php bin/console app:add-user` NOTE: THIS IS MANDATORY, THERE IS NO REGISTER WITHIN THE FRONTEND
> - Create a note
>   - `php bin/console app:add-note`

> ### Commands to configure API
> - `python3 -m venv venv`
> - `source venv/bin/activate`
> - `pip install -r requirements.txt`
>

> ### Routes
> - Backend: `http://localhost/8080` (Base endpoint is /api)
> - Frontend: `http://localhost:3000`
> - Api: `http://localhost:5050` (Base endpoint is /api)

> # Description
> ## Backend (PHP 8.4.5 + symfony 7.2): 
> - In this project I have decided to create a structure that would decouple responsabilities of each subject. 
> #### I have created the following structure:
>   - src
>       - Note (Feature related with notes)
>       - User (Feature related with User)
>       - Global (Folder to hold all global classes or traits)
>       - Auth (entrypoint responsible to for api authenticated routes)
>   - tests (All folders with tests)
>       - Command (Tests for all commands)
>       - Integration (Tests for controllers - api routes)
>       - Repository (Tests for repositories new methods)
>       - Unit (Unit tests on services, dtos and entities)
>
> For authentication I have decided to use JWT after user login, since this is a notes web solution, it will rely on the >user that is accessing it, for that purpose, I have created the logic for the user to be able to login (without register).
>
> There is CORS configured but without much detail
>
> Most of the controllers have validation before the services
>
> For the entities, I have decided to add createdAt and updatedAt on all of them filled automatically.
>
> Dto for create, update requests and for most of the responses (not delete)
>
> I have kept the config/jwt keys in order to be used on the api solution
>
>
> ## Frontend (Nodejs 18 + Vue3 + Vite): 
> - For this project I decided to make it simple, creating just 3 views (Login, List, Detail), the structure is:
>   - src
>       - router
>       - stores
>           - notes.ts (responsible to make api requests for notes)
>           - auth.ts (responsible to make api requests for authentication)
>       - utils
>           - api.ts (wrapper for api client)
>       - views
>           - LoginView.vue (Login page)
>           - NotesListView.vue (Notes list page for the logged user, you can delete a note here)
>           - NoteDetailView.vue (Note detail page, where you can add or edit a note)
>
> For this solution, I have used BootstrapVue3.
>
>
> ## Api (Flask with python 3.13): 
> - This is the most simple part, since I didn't had time to make it more complex
> - Folder structure:
>   - app
>       - controllers (all the routes without validations)
>       - middleware
>           - jwt_middleware.py (method responsible to decode the jwt using the key from the backend)
>       - models
>           - note (note entity)
>           - user (user entity)
>       - services
>           - notes_service.py (service responsible for business logic like, create, get, delete, update)
>
>

## My main objective with this was to create a backend responsible to interact with the frontend and to handle authentication, to share with the REST API, in order to decode the jwt and be authenticated to fetch data from database and return properly.
