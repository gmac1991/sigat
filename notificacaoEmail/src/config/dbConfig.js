import knex from 'knex';
import * as dotenv from 'dotenv';
dotenv.config();

let conn = knex({
  client: 'mysql2',
  connection: {
    host: process.env.DB_HOST,
    user: process.env.DB_USER_SIGAT,
    password: process.env.DB_PASSWORD_SIGAT,
    database: process.env.DB_DATABASE_SIGAT,
    port: process.env.DB_PORT
  }
});

let conn_otobo = knex({
  client: 'mysql2',
  connection: {
    host: process.env.DB_HOST,
    user: process.env.DB_USER_OTOBO,
    password: process.env.DB_PASSWORD_OTOBO,
    database: process.env.DB_DATABASE_OTOBO,
    port: process.env.DB_PORT
  }
});

export default {conn, conn_otobo};