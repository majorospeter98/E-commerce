<h1>Football Full Stack E-commerce Project with Vue.js, Laravel, Inertia.js and Tailwind</h1>

<h2>Explore and purchase your favorite team's kits and shorts</h2>
<p>In this project, you can do the following:</p>
<ul>
  <li>View selected kits and shorts.</li>
  <li>Add selected items to the cart.</li>
  <li>Buy the selected items after adding items to the cart.</li>

  <li>If you place an order while logged in, you can track what and how many items you’ve purchased.</li>
  <li>Check the number of items in your cart at any time.</li>
</ul>


<h2> ** Installation ** </h2> 

<p>   <strong> 1. Clone the repository </strong> </p>

<p>git clone https://github.com/majorospeter98/E-commerce.git </p>
<p> cd E-commerce </p>

<p>  <strong> 2. Install dependencies   </strong> </p>

<p> composer install </p>
<p> npm install </p>

<p>  <strong> 3. Configure environment variables </strong> </p>

<p> copy .env.example .env  </p>

<p>  <strong> 4. Generate the application key </strong>  </p>
 
<p> php artisan key:generate  </p>

<p>  <strong> 5. Create the SQLite database </strong>  </p>

<p> if not exist database\database.sqlite type nul > database\database.sqlite  </p>

<p> Make sure .env contains:  </p>

<p> DB_CONNECTION=sqlite  </p>

<p>  <strong> 6. Run database migrations </strong>  </p>

<p> php artisan migrate  </p>

<p>  <strong> 7. Start the application </strong>  </p>

<p> Start the Laravel server:  </p>

<p> php artisan serve l </p>

<p> In a separate terminal, start Vite: l </p>

<p> npm run dev l </p>
