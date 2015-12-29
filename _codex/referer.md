---
layout: page
title: Referer Checker
excerpt: Check your HTTP referer
categories: codex
script: [/js/referer.js]
---
<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>Variable</th>
        <th>Contents</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>HTTP_USER_AGENT</th>
        <td id="user-agent"></td>
      </tr>
      <tr>
        <td>HTTP_REFERER</th>
        <td id="referer"></td>
      </tr>
    </tbody>
  </table>
</div>

Back to the article:
  <q><a href="{{ '/article/referer-suppressor.html' | prepend: site.baseurl }}">Referer Suppressor for external link</a></q>