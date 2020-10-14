<template>
  <ais-instant-search :search-client="searchClient" :index-name="indexname">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <ais-hits>
          <ul slot-scope="{ items }">
            <li v-for="item in items" :key="item.objectID">
              <a :href="item.path">
                <ais-highlight attribute="title" :hit="item" />
              </a>
           </li>
          </ul>
        </ais-hits>
      </div>

      <div class="col-md-4">

        <div class="card">
          <div class="card-header">
            Search
          </div>
          <div class="card-body">

            <ais-configure :query="query"></ais-configure>

            <ais-search-box>
              <div slot-scope="{ currentRefinement, isSearchStalled, refine }">
                <input
                    class="form-control"
                    placeholder="Find a thread…"
                    type="search"
                    v-model="currentRefinement"
                    @input="refine($event.currentTarget.value)"
                >
                <span :hidden="!isSearchStalled">Loading...</span>
              </div>
            </ais-search-box>

          </div>
        </div>

        <div class="card mt-4">
          <div class="card-header">
            Filter By Channel
          </div>
          <div class="card-body">
            <ais-refinement-list attribute="channel.name"/>
          </div>
        </div>
      </div>
    </div>
  </ais-instant-search>
</template>

<script>

/*
  https://www.algolia.com/doc/guides/building-search-ui/what-is-instantsearch/vue/
 */

import algoliasearch from 'algoliasearch/lite';
//import 'instantsearch.css/themes/algolia-min.css';

export default {
  props: [
    'appid',
    'appkey',
    'indexname',
    'searchparam'
  ],

  data() {
    return {
      query: null,
      searchClient: algoliasearch(
          this.appid,
          this.appkey
      ),
    };
  },

  mounted(){
    this.query = this.searchparam;

  }
};
</script>

<style>
  .ais-RefinementList-list{
    margin: 0;
    padding:0;
    list-style:none;
  }
</style>