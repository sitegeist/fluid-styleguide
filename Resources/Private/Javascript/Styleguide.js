import "@babel/polyfill";
import "iframe-resizer/js/iframeResizer";

import '../Components/Atom/StyleguideSelect/StyleguideSelect';
import '../Components/Organism/StyleguideToolbar/StyleguideToolbar';
import '../Components/Atom/StyleguideScrollTop/StyleguideScrollTop';
import '../Components/Atom/ViewportNavigation/ViewportNavigation';
import '../Components/Molecule/EditFixtures/EditFixtures';

iFrameResize({ heightCalculationMethod: 'taggedElement' }, '.iframeResize');
