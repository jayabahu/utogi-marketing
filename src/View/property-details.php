<div class="property_details_box">
    <style scoped>
        section {
            display: grid;
            grid-template-columns: max-content 2fr;
            grid-row-gap: 10px;
            grid-column-gap: 20px;
        }
        .property_details_field{
            display: contents;
        }
    </style>

    <section>
        <div class="meta-options property_details_field">
               <label for="utogiId">Utogi Id</label>
               <input id="utogiId"
                      type="text"
                      name="utogiId"
                      disabled
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'utogiId', true ) ); ?>">
        </div>
    </section>
    
    <h3>Pricing</h3>
    <section>
        <div class="meta-options property_details_field">
               <label for="saleMethod">Sale Method</label>
               <input id="saleMethod"
                      type="text"
                      name="saleMethod"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'saleMethod', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
               <label for="price">Price</label>
               <input id="price"
                      type="text"
                      name="price"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'price', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
               <label for="priceDate">Date</label>
               <input id="priceDate"
                      type="text"
                      name="priceDate"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'priceDate', true ) ); ?>">
        </div>
    </section>

    <h3>General</h3>
    <section>
        <div class="meta-options property_details_field">
            <label for="streetNumber">Street Number</label>
            <input id="streetNumber"
                   type="text"
                   name="streetNumber"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'streetNumber', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
            <label for="unit">Unit</label>
            <input id="unit"
                   type="text"
                   name="unit"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'unit', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
            <label for="streetName">Street Name</label>
            <input id="streetName"
                   type="text"
                   name="streetName"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'streetName', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
            <label for="country">Country</label>
            <input id="country"
                   type="text"
                   name="country"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'country', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
            <label for="region">Region</label>
            <input id="region"
                   type="text"
                   name="region"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'region', true ) ); ?>">
        </div>

        <div class="meta-options property_details_field">
            <label for="city">City</label>
            <input id="city"
                   type="text"
                   name="city"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'city', true ) ); ?>">
        </div>

        <div class="meta-options property_details_field">
               <label for="suburb">Suburb</label>
               <input id="suburb"
                      type="text"
                      name="suburb"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'suburb', true ) ); ?>">
        </div>

        <div class="meta-options property_details_field">
               <label for="category">Category</label>
               <input id="category"
                      type="text"
                      name="category"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'category', true ) ); ?>">
        </div>

        <div class="meta-options property_details_field">
               <label for="type">Type</label>
               <input id="type"
                      type="text"
                      name="type"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'type', true ) ); ?>">
        </div>

    </section>

    <h3>Property Information</h3>
    <section>
      <div class="meta-options property_details_field">
             <label for="landArea">Land Area (㎡)</label>
             <input id="landArea"
                    type="text"
                    name="landArea"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'landArea', true ) ); ?>">
      </div>
      <div class="meta-options property_details_field">
             <label for="floorArea">Floor Area (㎡)</label>
             <input id="floorArea"
                    type="text"
                    name="floorArea"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'floorArea', true ) ); ?>">
      </div>
      <div class="meta-options property_details_field">
             <label for="livingRooms">Living Rooms</label>
             <input id="livingRooms"
                    type="text"
                    name="livingRooms"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'livingRooms', true ) ); ?>">
      </div>
        <div class="meta-options property_details_field">
            <label for="bathrooms">Bathrooms</label>
            <input id="bathrooms"
                   type="text"
                   name="bathrooms"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'bathrooms', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
            <label for="bedrooms">Bedrooms</label>
            <input id="bedrooms"
                   type="text"
                   name="bedrooms"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'bedrooms', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
               <label for="ensuites">Ensuite</label>
               <input id="ensuites"
                      type="text"
                      name="ensuites"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'ensuites', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
               <label for="dining">Dining</label>
               <input id="dining"
                      type="text"
                      name="dining"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'dining', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
               <label for="garages">Garages</label>
               <input id="garages"
                      type="text"
                      name="garages"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'garages', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
               <label for="carports">Carports</label>
               <input id="carports"
                      type="text"
                      name="carports"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'carports', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
               <label for="offStreetPark">Off-street Parks</label>
               <input id="offStreetPark"
                      type="text"
                      name="offStreetPark"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'offStreetPark', true ) ); ?>">
        </div>
    </section>

    <h3>Images</h3>
    <section>
        <div class="meta-options property_details_field">
               <label for="featuredImages">Featured Images</label>
               <input id="featuredImages"
                      type="text"
                      name="featuredImages"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'featuredImages', true ) ); ?>">
        </div>
        <div class="meta-options property_details_field">
               <label for="images">Images</label>
               <input id="images"
                      type="text"
                      name="images"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'images', true ) ); ?>">
        </div>
    </section>

    <h3>Agents</h3>
    <section>
        <div class="meta-options property_details_field">
               <label for="agents">Agents</label>
               <input id="agents"
                      type="text"
                      name="agents"
                      value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'agents', true ) ); ?>">
        </div>
    </section>
</div>