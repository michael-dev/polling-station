var zettelconfig = null

function zettelconfiginit() {
  $.ajax('lib/config.ini', {'async': false, 'dataType': 'text'})
   .success(function (values, status, req) {
     if (typeof(values) != 'string') {
       alert('Die Konfigurationsdatei konnte nicht geladen werden. Es liegen keine Informationen über die Gremien vor.');
     }
     cfglines = values.split(/\r?\n/);
     zettelconfig = {};
     var section = '';
     for (var k in cfglines) {
       var line = cfglines[k];
       line = line.replace(/#.*/, "");
       line = line.replace(/[\r\n]/g, "");
       line = line.replace(/^\s*/g, "");
       if (line == '') { continue; }
       // parse section header
       var m = line.match(/^\[(.*)\]$/);
       if (m) {
         section = m[1];
         zettelconfig[section] = {};
         continue;
       }
       // assignments
       if (!zettelconfig[section]) { zettelconfig[section] = {}; }
       var m = line.match(/^([^=]*)=(.*)$/);
       if (!m) {
         m = [line,line,true];
       }
       if (m[1].substr(-2) == '[]') {
         m[1] = m[1].substring(0,m[1].length-2);
         if (!zettelconfig[section][m[1]]) { zettelconfig[section][m[1]] = []; }
         zettelconfig[section][m[1]].push(m[2]);
       } else {
         zettelconfig[section][m[1]] = m[2];
       }
     }
    })
   .error(function () { alert("Die Stimmzettel konnten nicht geladen werden."); });
}

if (!Object.keys) {
    Object.keys = function (obj) {
        var keys = [],
            k;
        for (k in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, k)) {
                keys.push(k);
            }
        }
        return keys;
    };
}

