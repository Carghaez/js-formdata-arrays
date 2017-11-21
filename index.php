<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>JS FormData Arrays</title>
</head>
<body>
  <script type="text/javascript">
    var DATA = {};

    function splitFormInputName(inputName) {
      return inputName.split('[').map((key) => {
        if (key.charAt(key.length-1) == ']') {
          return key.slice(0, -1);
        }
        return key;
      });
    }

    function firstEmptyIndexOf(data) {
      let i = 0;
      while(i in data) {
        i++;
      }
      return i;
    }

    function isEmpty(obj) {
      return (typeof obj === 'undefined' || Object.keys(obj).length === 0);
    }

    function getChildPropertyName(parent, child) {
      if (typeof parent !== 'undefined') {
        let keys = Object.keys(parent);
        for (let i = 0; i < keys.length; ++i) {
          if (parent[keys[i]] === child) {
            return keys[i];
          }
        }
      }
      return undefined;
    }

    function refactorChild(parent, child, key) {
      let isIndexKey = !isNaN(Number(key));

      if (!Array.isArray(child) && isEmpty(child) && isIndexKey) {
        return [];
      }

      if (Array.isArray(child) && !isIndexKey) {
        return child.reduce((acc, cur, i) => {
          acc[i] = cur;
          return acc;
        }, {});
      }

      return child;
    }

    function createChild(parent, child, key, value = undefined)
    {
      if (key === '') {
        key = firstEmptyIndexOf(child);
      }

      let childKey = getChildPropertyName(parent, child);
      if (typeof childKey !== 'undefined') {
        child = parent[childKey] = refactorChild(parent, child, key);
      }

      if (typeof value !== 'undefined') {
        child[key] = value;
      } else {
        if (typeof child[key] !== 'object') {
          child[key] = {};
        }
      }

      return { parent: child, key: key };
    }

    function appendToData(inputName, inputValue) {
      keys = splitFormInputName(inputName);
      let child = DATA;
      let struct = {
        parent: undefined,
        key: ''
      };
      for (let i = 0; i < keys.length; ++i) {
        struct = (i === keys.length - 1)
          ? createChild(struct.parent, child, keys[i], inputValue)
          : createChild(struct.parent, child, keys[i]);
        child = struct.parent[struct.key];
      }
    }

    function main() {
      let form = document.getElementById('form');
      let isEmptyPOST = <?=empty($_POST)?'true':'false'?>;
      if (isEmptyPOST) {
        form.submit();
      }
      for (child of form.children) {
        if (child.name !== '') {
          appendToData(child.name, child.value);
        }
      }
      document.getElementById('jsData').textContent = JSON.stringify(DATA, null, '    ');
    }
    document.addEventListener('DOMContentLoaded', main);
  </script>

  <form method="POST" id="form">
    <input type="hidden" name="test[1][]" value="T1" />
    <input type="hidden" name='test[1][foo]' value="T2" />
    <input type="hidden" name="test[1][bar]" value="T3" />
    <input type="hidden" name="test[1][bar][][w]" value="T4" />
    <input type="hidden" name="test[1][bar][][h]" value="T5" />
    <input type="hidden" name="test[new][]" value="T6" />
  </form>

  <h2>JS DATA</h2>
  <pre id="jsData"></pre>

  <h2>$_POST</h2>
  <pre><?=json_encode($_POST, JSON_PRETTY_PRINT);?></pre>
</body>
</html>